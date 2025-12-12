from flask import Flask, request, jsonify
from flask_cors import CORS
from datetime import datetime
import mysql.connector
import random
import json
import re
import uuid
from functools import wraps

app = Flask(__name__)
CORS(app)

# Configuración de base de datos
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'tec_externo',
    'password': '4i8fxYzH`ZJ-UE-D=L4.',
    'database': 'apphotel',
    'port': 3306
}

# Importar módulos
from intent_processor import detect_intent
from database_handler import (
    connect_db, fetch_habitaciones, fetch_servicios,
    register_user, login_user, create_reserva, create_venta,
    create_pago, get_user_session, update_session,
    check_habitacion_disponible, get_cliente_by_email
)
from payment_handler import generate_qr_payment, verify_payment_status

# Estados del chat para guiar al usuario
CHAT_STATES = {
    'idle': 'Esperando interacción',
    'asking_habitaciones': 'Mostrando habitaciones',
    'asking_servicios': 'Mostrando servicios',
    'registering': 'Registrando usuario',
    'logging_in': 'Iniciando sesión',
    'selecting_habitacion': 'Seleccionando habitación',
    'selecting_fechas': 'Seleccionando fechas',
    'confirming_reserva': 'Confirmando reserva',
    'selecting_payment': 'Seleccionando método de pago',
    'processing_payment': 'Procesando pago',
    'payment_completed': 'Pago completado'
}

# Variables de sesión por usuario (en producción usar Redis o DB)
user_sessions = {}

def get_or_create_session(user_id=None):
    """Obtener o crear sesión de usuario"""
    session_id = user_id or request.remote_addr
    if session_id not in user_sessions:
        user_sessions[session_id] = {
            'state': 'idle',
            'data': {},
            'user': None,
            'step': 0
        }
    return user_sessions[session_id]

# === HANDLERS PARA CADA ESTADO ===
def handle_idle_state(message, session):
    """Manejar estado inicial"""
    message_lower = message.lower()
    
    # Saludos
    if re.search(r'\b(hola|buenos días|buenas tardes|buenas noches|saludos|hey|hi|hello)\b', message_lower):
        return (
            "¡Hola! 👋 Bienvenido al Hotel ICI. 🌟\n\n"
            "Soy ICI Assistant y puedo ayudarte a:\n"
            "📋 Ver habitaciones disponibles\n"
            "🛎️  Conocer nuestros servicios\n"
            "🏨 Realizar una reserva\n"
            "💳 Procesar el pago\n\n"
            "¿En qué puedo ayudarte hoy?\n"
            "Puedes decir: 'Quiero reservar' o 'Ver habitaciones'"
        )
    
    # Ver habitaciones
    if re.search(r'\b(habitaciones|cuartos|alojamiento|ver habitaciones|disponibles)\b', message_lower):
        session['state'] = 'asking_habitaciones'
        habs = fetch_habitaciones()
        return format_habitaciones_response(habs)
    
    # Ver servicios
    if re.search(r'\b(servicios|amenities|comodidades|spa|gimnasio|desayuno)\b', message_lower):
        session['state'] = 'asking_servicios'
        servs = fetch_servicios()
        return format_servicios_response(servs)
    
    # Iniciar reserva
    if re.search(r'\b(reservar|reserva|quiero reservar|hacer reserva|alojarme|quedarme)\b', message_lower):
        session['state'] = 'registering'
        session['step'] = 1
        return (
            "¡Excelente! Vamos a realizar tu reserva. 🏨\n\n"
            "Primero necesito que te registres o inicies sesión.\n\n"
            "Si ya tienes cuenta, escribe tu email y contraseña separados por coma:\n"
            "Ejemplo: usuario@email.com, contraseña123\n\n"
            "Si eres nuevo, escribe 'registrarme' para crear una cuenta."
        )
    
    # Registro directo
    if re.search(r'\b(registrarme|crear cuenta|nuevo usuario|soy nuevo)\b', message_lower):
        session['state'] = 'registering'
        session['step'] = 2
        return (
            "👤 **Registro de nuevo usuario**\n\n"
            "Por favor, proporciona la siguiente información separada por comas:\n"
            "📋 Nombre, Apellido, DNI, Email, Teléfono, Dirección, Contraseña\n\n"
            "Ejemplo: Juan, Pérez, 12345678, juan@email.com, 912345678, Calle Falsa 123, pass123"
        )
    
    # Ayuda
    if re.search(r'\b(ayuda|qué puedes hacer|funciones|opciones)\b', message_lower):
        return (
            "🤖 **Funciones disponibles:**\n\n"
            "🏨 **Reservas:**\n"
            "- Ver habitaciones disponibles\n"
            "- Realizar una reserva\n"
            "- Procesar el pago\n\n"
            "🛎️ **Servicios:**\n"
            "- Ver servicios del hotel\n"
            "- Agregar servicios a tu reserva\n\n"
            "👤 **Cuenta:**\n"
            "- Registrarte como cliente\n"
            "- Iniciar sesión\n\n"
            "💰 **Pagos:**\n"
            "- Pago con tarjeta\n"
            "- Pago con QR\n\n"
            "¿Qué te gustaría hacer?"
        )
    
    # Agradecimientos
    if re.search(r'\b(gracias|agradezco|thank you|thanks|ty)\b', message_lower):
        return random.choice([
            "¡Muchas gracias a usted! 😊 Esperamos atenderle pronto en el Hotel ICI.",
            "A usted, estimado cliente. 🌟 ¡Que tenga un excelente día!",
            "El placer es nuestro. ¡Le esperamos con los brazos abiertos! 🏨"
        ])
    
    # Si no se entiende, sugerir opciones
    return (
        "No entendí completamente tu pregunta. 🤔\n\n"
        "Puedes preguntarme sobre:\n"
        "• Habitaciones disponibles 🏨\n"
        "• Servicios del hotel 🛎️\n"
        "• Realizar una reserva 📅\n"
        "• Métodos de pago 💳\n\n"
        "O si quieres ayuda, escribe: 'ayuda'"
    )

def handle_registering_state(message, session):
    """Manejar registro de usuario"""
    if session['step'] == 1:  # Paso 1: Opción login/registro
        if 'registrarme' in message.lower():
            session['step'] = 2
            return (
                "👤 **Registro de nuevo usuario**\n\n"
                "Por favor, proporciona la siguiente información separada por comas:\n"
                "📋 Nombre, Apellido, DNI, Email, Teléfono, Dirección, Contraseña\n\n"
                "Ejemplo: Juan, Pérez, 12345678, juan@email.com, 912345678, Calle Falsa 123, pass123"
            )
        else:
            # Intentar login
            try:
                parts = [p.strip() for p in message.split(',')]
                if len(parts) >= 2:
                    email, password = parts[0], parts[1]
                    user = login_user(email, password)
                    if user:
                        session['user'] = user
                        session['state'] = 'selecting_habitacion'
                        session['step'] = 0
                        return (
                            f"¡Bienvenido de nuevo, {user['nombre']}! 👋\n\n"
                            "Ahora vamos a seleccionar tu habitación.\n\n"
                            "Estas son nuestras habitaciones disponibles:\n"
                            f"{format_habitaciones_response(fetch_habitaciones())}"
                        )
            except:
                pass
            return "Por favor, escribe tu email y contraseña separados por coma, o 'registrarme' para crear cuenta."
    
    elif session['step'] == 2:  # Paso 2: Registro completo
        try:
            parts = [p.strip() for p in message.split(',')]
            if len(parts) >= 7:
                nombre, apellido, dni, email, telefono, direccion, password = parts[:7]
                
                # Registrar usuario
                user_id = register_user(
                    nombre, apellido, dni, email, telefono, 
                    direccion, password
                )
                
                if user_id:
                    session['user'] = {
                        'id': user_id,
                        'nombre': nombre,
                        'email': email,
                        'cliente_id': user_id
                    }
                    session['state'] = 'selecting_habitacion'
                    session['step'] = 0
                    return (
                        f"✅ **¡Registro exitoso, {nombre}!**\n\n"
                        "Tu cuenta ha sido creada correctamente.\n\n"
                        "Ahora vamos a seleccionar tu habitación.\n\n"
                        "🏨 **Habitaciones disponibles:**\n"
                        f"{format_habitaciones_response(fetch_habitaciones())}"
                    )
                else:
                    return "❌ Error al registrar. El email o DNI ya existen. Intenta con otros datos."
            else:
                return "❌ Por favor, proporciona todos los datos solicitados separados por comas."
        except Exception as e:
            return f"❌ Error en el registro: {str(e)}. Por favor, verifica los datos."

def handle_selecting_habitacion_state(message, session):
    """Manejar selección de habitación"""
    habs = fetch_habitaciones()
    
    # Buscar habitación por tipo
    for hab in habs:
        if hab['tipo'].lower() in message.lower():
            session['data']['habitacion'] = hab
            session['state'] = 'selecting_fechas'
            session['step'] = 1
            return (
                f"✅ Has seleccionado: **{hab['tipo']}**\n"
                f"💵 Precio por noche: ${hab['precio_noche']:.2f}\n"
                f"👥 Capacidad: {hab['capacidad']} personas\n\n"
                "📅 **Ahora necesito las fechas de tu estadía:**\n\n"
                "Por favor, escribe las fechas de entrada y salida en formato:\n"
                "YYYY-MM-DD, YYYY-MM-DD\n\n"
                "Ejemplo: 2025-12-25, 2026-01-25\n\n"
                "O escribe 'hoy' para hoy y mañana, o 'fin de semana' para este fin de semana."
            )
    
    # Si no se reconoce, mostrar opciones
    return (
        "❌ No reconozco esa habitación. Estas son nuestras opciones:\n\n"
        f"{format_habitaciones_response(habs)}\n\n"
        "Por favor, escribe el tipo de habitación que deseas (ej: 'Suite Ejecutiva')."
    )

def handle_selecting_fechas_state(message, session):
    """Manejar selección de fechas"""
    try:
        # Opciones predefinidas
        if 'hoy' in message.lower():
            fecha_entrada = datetime.now().strftime('%Y-%m-%d')
            fecha_salida = (datetime.now().day + 1).strftime('%Y-%m-%d')
        elif 'fin de semana' in message.lower():
            # Encontrar próximo viernes
            today = datetime.now()
            days_to_friday = (4 - today.weekday()) % 7
            friday = today.replace(day=today.day + days_to_friday)
            fecha_entrada = friday.strftime('%Y-%m-%d')
            fecha_salida = (friday.replace(day=friday.day + 2)).strftime('%Y-%m-%d')
        else:
            # Parsear fechas del usuario
            parts = [p.strip() for p in message.split(',')]
            if len(parts) >= 2:
                fecha_entrada = parts[0]
                fecha_salida = parts[1]
            else:
                return "❌ Por favor, escribe las dos fechas separadas por coma (ej: 2024-12-25, 2024-12-30)"
        
        # Validar fechas
        entrada = datetime.strptime(fecha_entrada, '%Y-%m-%d')
        salida = datetime.strptime(fecha_salida, '%Y-%m-%d')
        
        if salida <= entrada:
            return "❌ La fecha de salida debe ser posterior a la fecha de entrada."
        
        if entrada < datetime.now():
            return "❌ La fecha de entrada no puede ser en el pasado."
        
        # Calcular noches y precio
        noches = (salida - entrada).days
        precio_noche = session['data']['habitacion']['precio_noche']
        precio_total = precio_noche * noches
        
        session['data']['fecha_entrada'] = fecha_entrada
        session['data']['fecha_salida'] = fecha_salida
        session['data']['noches'] = noches
        session['data']['precio_total'] = precio_total
        
        session['state'] = 'confirming_reserva'
        session['step'] = 1
        
        return (
            f"📅 **Resumen de tu selección:**\n\n"
            f"🏨 Habitación: {session['data']['habitacion']['tipo']}\n"
            f"📅 Check-in: {fecha_entrada}\n"
            f"📅 Check-out: {fecha_salida}\n"
            f"🌙 Noches: {noches}\n"
            f"💰 Precio por noche: ${precio_noche:.2f}\n"
            f"💵 **Total: ${precio_total:.2f}**\n\n"
            "¿Deseas confirmar esta reserva?\n"
            "Responde 'sí' para confirmar o 'no' para modificar."
        )
    
    except ValueError:
        return "❌ Formato de fecha incorrecto. Usa YYYY-MM-DD (ej: 2024-12-25)."
    except Exception as e:
        return f"❌ Error: {str(e)}"

def handle_confirming_reserva_state(message, session):
    """Manejar confirmación de reserva"""
    if message.lower() in ['sí', 'si', 'confirmar', 'ok', 'aceptar']:
        # try:
            # Crear reserva en la base de datos
        user = session['user']
        habitacion = session['data']['habitacion']
        
        reserva_id = create_reserva(
            cliente_id=user['cliente_id'],
            habitacion_id=habitacion['id'],
            fecha_entrada=session['data']['fecha_entrada'],
            fecha_salida=session['data']['fecha_salida'],
            noches=session['data']['noches'],
            precio_total=session['data']['precio_total']
        )
        
        if reserva_id:
            # PRIMERO CREAR LA VENTA
            venta_id = create_venta(
                cliente_id=user['cliente_id'], 
                monto_total=session['data']['precio_total'],
                estado='pendiente'  # Pendiente porque aún no se ha pagado
            )
            
            if venta_id:
                # ACTUALIZAR LA RESERVA CON EL VENTA_ID
                # Necesitamos una función para actualizar la reserva
                try:
                    conn = connect_db()
                    cursor = conn.cursor()
                    cursor.execute(
                        "UPDATE reservas SET venta_id = %s WHERE id = %s",
                        (venta_id, reserva_id)
                    )
                    conn.commit()
                    conn.close()
                except Exception as e:
                    print(f"⚠️ No se pudo actualizar reserva con venta_id: {e}")
                    # No es crítico, continuar
                
                session['data']['reserva_id'] = reserva_id
                session['data']['venta_id'] = venta_id
                session['state'] = 'selecting_payment'
                session['step'] = 1
                
                return (
                    f"✅ **¡Reserva confirmada!**\n\n"
                    f"📋 Número de reserva: #{reserva_id:06d}\n"
                    f"💵 Total a pagar: ${session['data']['precio_total']:.2f}\n\n"
                    "💳 **Selecciona método de pago:**\n\n"
                    "1. 💳 **Pago con tarjeta** (procesamiento inmediato)\n"
                    "2. 📱 **Pago con QR** (escanea y paga)\n\n"
                    "Responde 'tarjeta' o 'qr' para continuar."
                )
            else:
                return "❌ Error al crear la venta. Por favor, intenta de nuevo."
        else:
            return "❌ Error al crear la reserva. Por favor, intenta de nuevo."
        
        # except Exception as e:
        #     return f"❌ Error: {str(e)}"
    
    else:
        session['state'] = 'selecting_habitacion'
        session['step'] = 0
        return "Reserva cancelada. ¿Qué habitación te gustaría?"

def handle_selecting_payment_state(message, session):
    """Manejar selección de método de pago"""
    if 'tarjeta' in message.lower():
        session['data']['metodo_pago'] = 'tarjeta'
        session['state'] = 'processing_payment'
        session['step'] = 1
        
        return (
            "💳 **Pago con Tarjeta**\n\n"
            "Por favor, proporciona los datos de tu tarjeta en este formato:\n"
            "📋 Número, Nombre en tarjeta, Fecha Exp (MM/AA), CVV\n\n"
            "Ejemplo: 4111111111111111, JUAN PEREZ, 12/25, 123\n\n"
            "⚠️ **Nota:** Esta es una demostración. En producción, usa un gateway de pago seguro."
        )
    
    elif 'qr' in message.lower():
        session['data']['metodo_pago'] = 'qr'
        session['state'] = 'processing_payment'
        session['step'] = 2
        
        # Generar QR de pago
        reserva_id = session['data']['reserva_id']
        monto = session['data']['precio_total']
        qr_data = generate_qr_payment(reserva_id, monto)
        
        session['data']['qr_data'] = qr_data
        
        return (
            f"📱 **Pago con QR**\n\n"
            f"💰 Monto: ${monto:.2f}\n"
            f"📋 Reserva: #{reserva_id:06d}\n\n"
            "Escanea el siguiente código QR con tu aplicación de pagos:\n\n"
            f"🔗 **URL para pagar:** {qr_data['payment_url']}\n"
            f"📊 **Código QR generado** (en producción mostraríamos la imagen)\n\n"
            "Una vez realizado el pago, escribe 'verificar pago' para confirmar."
        )
    
    else:
        return "Por favor, selecciona 'tarjeta' o 'qr' como método de pago."

def handle_processing_payment_state(message, session):
    """Manejar procesamiento de pago"""
    if session['step'] == 1:  # Procesando tarjeta
        try:
            # Validar formato de tarjeta (simulado)
            parts = [p.strip() for p in message.split(',')]
            if len(parts) >= 4:
                numero, nombre, expiracion, cvv = parts[:4]
                
                # Datos del pago
                user = session['user']
                reserva_id = session['data']['reserva_id']
                venta_id = session['data']['venta_id']  # YA EXISTE
                monto = session['data']['precio_total']
                
                # VERIFICAR QUE TENEMOS VENTA_ID
                if not venta_id:
                    return "❌ Error: No se encontró información de venta. Por favor, reinicia el proceso."
                
                # 1. Crear registro de pago
                referencia = f"TARJ-{reserva_id:06d}-{int(datetime.now().timestamp())}"
                pago_id = create_pago(
                    venta_id=venta_id,
                    metodo_pago='tarjeta',
                    monto=monto,
                    estado='completado',
                    referencia=referencia,
                    detalles={
                        'tarjeta': numero[-4:],
                        'nombre': nombre,
                        'expiracion': expiracion
                    }
                )
                
                if not pago_id:
                    return "❌ Error al crear el registro de pago."
                
                # 2. Actualizar venta a 'completada'
                try:
                    conn = connect_db()
                    cursor = conn.cursor()
                    cursor.execute(
                        "UPDATE ventas SET estado = 'completada' WHERE id = %s",
                        (venta_id,)
                    )
                    conn.commit()
                    conn.close()
                    print(f"✅ Venta {venta_id} actualizada a 'completada'")
                except Exception as e:
                    print(f"⚠️ Error actualizando venta: {e}")
                
                # 3. Actualizar reserva a 'confirmada' (por si acaso)
                try:
                    conn = connect_db()
                    cursor = conn.cursor()
                    cursor.execute(
                        "UPDATE reservas SET estado = 'confirmada' WHERE id = %s",
                        (reserva_id,)
                    )
                    conn.commit()
                    conn.close()
                    print(f"✅ Reserva {reserva_id} actualizada a 'confirmada'")
                except Exception as e:
                    print(f"⚠️ Error actualizando reserva: {e}")
                
                session['data']['pago_id'] = pago_id
                session['state'] = 'payment_completed'
                
                return (
                    f"✅ **¡Pago procesado exitosamente!**\n\n"
                    f"💳 Método: Tarjeta de crédito\n"
                    f"💰 Monto: ${monto:.2f}\n"
                    f"📋 Referencia: {referencia}\n"
                    f"🏨 Reserva: #{reserva_id:06d}\n"
                    f"📊 Venta: #{venta_id:06d}\n"
                    f"💳 Pago: #{pago_id:06d}\n\n"
                    "🎉 **¡Tu reserva está confirmada!**\n\n"
                    "Recibirás un email con los detalles de tu reserva.\n"
                    "¡Gracias por elegir Hotel ICI! 🌟"
                )
            else:
                return "❌ Formato incorrecto. Usa: Número, Nombre, Expiración (MM/AA), CVV"
        
        except Exception as e:
            return f"❌ Error en el pago: {str(e)}"
    
    elif session['step'] == 2:  # Verificando pago QR
        if 'verificar pago' in message.lower():
            # Verificar estado del pago QR
            reserva_id = session['data']['reserva_id']
            venta_id = session['data']['venta_id']  # YA EXISTE
            qr_data = session['data']['qr_data']
            monto = session['data']['precio_total']
            user = session['user']
            
            # Simular verificación (en producción, consultar gateway)
            pago_verificado = verify_payment_status(qr_data['payment_id'])
            
            if pago_verificado:
                # VERIFICAR QUE TENEMOS VENTA_ID
                if not venta_id:
                    return "❌ Error: No se encontró información de venta. Por favor, reinicia el proceso."
                
                # 1. Crear registro de pago QR
                referencia = f"QR-{reserva_id:06d}-{int(datetime.now().timestamp())}"
                pago_id = create_pago(
                    venta_id=venta_id,
                    metodo_pago='qr',
                    monto=monto,
                    estado='completado',
                    referencia=referencia,
                    detalles={
                        'payment_id': qr_data['payment_id']
                    }
                )
                
                if not pago_id:
                    return "❌ Error al crear el registro de pago."
                
                # 2. Actualizar venta a 'completada'
                try:
                    conn = connect_db()
                    cursor = conn.cursor()
                    cursor.execute(
                        "UPDATE ventas SET estado = 'completada' WHERE id = %s",
                        (venta_id,)
                    )
                    conn.commit()
                    conn.close()
                    print(f"✅ Venta {venta_id} actualizada a 'completada'")
                except Exception as e:
                    print(f"⚠️ Error actualizando venta: {e}")
                
                # 3. Actualizar reserva a 'confirmada'
                try:
                    conn = connect_db()
                    cursor = conn.cursor()
                    cursor.execute(
                        "UPDATE reservas SET estado = 'confirmada' WHERE id = %s",
                        (reserva_id,)
                    )
                    conn.commit()
                    conn.close()
                    print(f"✅ Reserva {reserva_id} actualizada a 'confirmada'")
                except Exception as e:
                    print(f"⚠️ Error actualizando reserva: {e}")
                
                session['data']['pago_id'] = pago_id
                session['state'] = 'payment_completed'
                
                return (
                    f"✅ **¡Pago verificado exitosamente!**\n\n"
                    f"📱 Método: Pago QR\n"
                    f"💰 Monto: ${monto:.2f}\n"
                    f"📋 Referencia: {referencia}\n"
                    f"🏨 Reserva: #{reserva_id:06d}\n"
                    f"📊 Venta: #{venta_id:06d}\n"
                    f"💳 Pago: #{pago_id:06d}\n\n"
                    "🎉 **¡Tu reserva está confirmada!**\n\n"
                    "Recibirás un email con los detalles de tu reserva.\n"
                    "¡Gracias por elegir Hotel ICI! 🌟"
                )
            else:
                return "❌ Pago no verificado. Asegúrate de haber realizado el pago y escribe 'verificar pago' de nuevo."
        else:
            return "Por favor, realiza el pago con el QR y luego escribe 'verificar pago'."

# === FUNCIONES DE FORMATO ===
def format_habitaciones_response(habs):
    if not habs:
        return "❌ No hay habitaciones disponibles en este momento."
    
    lines = []
    for h in habs:
        desc = f" - {h['descripcion']}" if h['descripcion'] else ""
        lines.append(
            f"• **{h['tipo']}**{desc}\n"
            f"  👥 Capacidad: {h['capacidad']} personas\n"
            f"  💵 Precio: ${h['precio_noche']:.2f}/noche\n"
        )
    
    return "🏨 **HABITACIONES DISPONIBLES:**\n\n" + "\n".join(lines)

def format_servicios_response(servs):
    if not servs:
        return "❌ No hay servicios adicionales disponibles."
    
    lines = []
    for s in servs:
        precio = "Gratuito" if s['precio'] == 0 else f"${s['precio']:.2f}"
        lines.append(f"• **{s['nombre']}** - {s['descripcion']} ({precio})")
    
    return "🛎️ **SERVICIOS DISPONIBLES:**\n\n" + "\n".join(lines)

# === MAIN PROCESSING FUNCTION ===
def process_message(message, session):
    """Procesar mensaje según el estado actual"""
    state = session['state']
    
    # Mapeo de estados a handlers
    handlers = {
        'idle': handle_idle_state,
        'asking_habitaciones': handle_idle_state,  # Reutilizar
        'asking_servicios': handle_idle_state,     # Reutilizar
        'registering': handle_registering_state,
        'logging_in': handle_registering_state,
        'selecting_habitacion': handle_selecting_habitacion_state,
        'selecting_fechas': handle_selecting_fechas_state,
        'confirming_reserva': handle_confirming_reserva_state,
        'selecting_payment': handle_selecting_payment_state,
        'processing_payment': handle_processing_payment_state,
        'payment_completed': lambda m, s: (
            "🎉 ¡Reserva y pago completados!\n\n"
            "Puedes iniciar una nueva consulta escribiendo 'hola' o 'ayuda'."
        )
    }
    
    # Obtener handler
    handler = handlers.get(state, handle_idle_state)
    return handler(message, session)

# === RUTA DEL CHAT ===
@app.route('/chat', methods=['POST'])
def chat():
    try:
        data = request.get_json()
        message = data.get('message', '').strip()
        user_id = data.get('user_id')  # En producción, usar token de sesión
        
        if not message:
            return jsonify({'reply': 'Por favor, escribe tu mensaje.'})
        
        # Obtener sesión del usuario
        session = get_or_create_session(user_id)
        
        # Procesar mensaje
        reply = process_message(message, session)
        
        # Guardar sesión (en producción, guardar en DB o Redis)
        if user_id:
            user_sessions[user_id] = session
        
        return jsonify({
            'reply': reply,
            'state': session['state'],
            'user_id': user_id or request.remote_addr
        })
        
    except Exception as e:
        print(f"❌ Error en /chat: {str(e)}")
        return jsonify({
            'reply': 'Lo siento, ocurrió un error. Por favor, intenta de nuevo.',
            'error': str(e)
        }), 500

# === RUTA PARA REINICIAR SESIÓN ===
@app.route('/reset', methods=['POST'])
def reset_chat():
    try:
        data = request.get_json()
        user_id = data.get('user_id')
        
        if user_id in user_sessions:
            user_sessions[user_id] = {
                'state': 'idle',
                'data': {},
                'user': None,
                'step': 0
            }
        
        return jsonify({
            'success': True,
            'message': 'Chat reiniciado'
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

# === INFORMACIÓN DEL SISTEMA ===
@app.route('/info', methods=['GET'])
def system_info():
    """Información del sistema del chatbot"""
    return jsonify({
        'name': 'Hotel ICI Chatbot',
        'version': '1.0.0',
        'features': [
            'Registro de usuarios',
            'Búsqueda de habitaciones',
            'Reservas en tiempo real',
            'Pagos con tarjeta y QR',
            'Gestión de servicios'
        ],
        'database': 'Connected' if connect_db() else 'Disconnected',
        'active_sessions': len(user_sessions)
    })

if __name__ == '__main__':
    print("🚀 Hotel ICI Chatbot iniciando...")
    print(f"✅ Base de datos: {'Conectada' if connect_db() else 'Error de conexión'}")
    print(f"🌐 Servidor corriendo en http://127.0.0.1:8001")
    print(f"📞 Endpoints disponibles:")
    print(f"   • POST /chat - Para enviar mensajes")
    print(f"   • POST /reset - Para reiniciar chat")
    print(f"   • GET /info - Información del sistema")
    app.run(host='127.0.0.1', port=8001, debug=False)
