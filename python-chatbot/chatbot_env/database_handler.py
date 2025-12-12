import mysql.connector
from datetime import datetime
import hashlib
import uuid
import bcrypt


DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'tec_externo',
    'password': '4i8fxYzH`ZJ-UE-D=L4.',
    'database': 'apphotel',
    'port': 3306
}

def connect_db():
    """Establecer conexión a la base de datos"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except Exception as e:
        print(f"❌ Error conectando a DB: {str(e)}")
        return None

def hash_password(password):
    """Hashear contraseña con bcrypt (compatible con Laravel)"""
    # Laravel usa bcrypt con cost factor de 10 por defecto
    salt = bcrypt.gensalt(rounds=10)
    hashed = bcrypt.hashpw(password.encode('utf-8'), salt)
    
    # Convertir de $2b$ (formato bcrypt de Python) a $2y$ (formato Laravel)
    laravel_hash = '$2y$' + hashed.decode('utf-8')[4:]
    return laravel_hash

# === USUARIOS ===
def register_user(nombre, apellido, dni, email, telefono, direccion, password):
    """Registrar un nuevo usuario/cliente"""
    try:
        conn = connect_db()
        if not conn:
            return None
        
        cursor = conn.cursor()
        
        # Verificar si email o DNI ya existen
        cursor.execute(
            "SELECT id FROM clientes WHERE email = %s OR dni = %s",
            (email, dni)
        )
        if cursor.fetchone():
            return None
        
        # Insertar cliente
        cursor.execute(
            """
            INSERT INTO clientes (nombre, apellido, dni, email, telefono, direccion, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
            """,
            (nombre, apellido, dni, email, telefono, direccion)
        )
        cliente_id = cursor.lastrowid
        
        # Crear usuario para login CON BCRYPT
        hashed_pwd = hash_password(password)  # Esta función ahora usa bcrypt
        
        cursor.execute(
            """
            INSERT INTO users (name, email, password, tipo, cliente_id, created_at, updated_at)
            VALUES (%s, %s, %s, 'cliente', %s, NOW(), NOW())
            """,
            (f"{nombre} {apellido}", email, hashed_pwd, cliente_id)
        )
        
        conn.commit()
        conn.close()
        
        return cliente_id
        
    except Exception as e:
        print(f"❌ Error en registro: {str(e)}")
        return None

def login_user(email, password):
    """Autenticar usuario"""
    try:
        conn = connect_db()
        if not conn:
            return None
        
        cursor = conn.cursor(dictionary=True)
        
        # Buscar usuario por email (sin verificar contraseña aún)
        cursor.execute(
            """
            SELECT u.*, c.id as cliente_id, c.nombre, c.apellido, c.dni, c.telefono, c.direccion
            FROM users u
            JOIN clientes c ON u.cliente_id = c.id
            WHERE u.email = %s
            """,
            (email,)
        )
        
        user = cursor.fetchone()
        conn.close()
        
        if not user:
            return None
        
        # Verificar contraseña con bcrypt
        hashed_pwd = user['password']
        
        # Convertir de $2y$ (Laravel) a $2b$ (bcrypt Python)
        if hashed_pwd.startswith('$2y$'):
            hashed_pwd = '$2b$' + hashed_pwd[4:]
        
        if bcrypt.checkpw(password.encode('utf-8'), hashed_pwd.encode('utf-8')):
            return user
        else:
            return None
        
    except Exception as e:
        print(f"❌ Error en login: {str(e)}")
        return None


def get_cliente_by_email(email):
    """Obtener cliente por email"""
    try:
        conn = connect_db()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute(
            "SELECT * FROM clientes WHERE email = %s",
            (email,)
        )
        
        cliente = cursor.fetchone()
        conn.close()
        
        return cliente
        
    except Exception as e:
        print(f"❌ Error obteniendo cliente: {str(e)}")
        return None

# === HABITACIONES Y SERVICIOS ===
def fetch_habitaciones():
    """Obtener habitaciones disponibles"""
    try:
        conn = connect_db()
        cursor = conn.cursor(dictionary=True)
        
        # Obtener habitaciones disponibles (no reservadas en las fechas solicitadas)
        cursor.execute(
            """
            SELECT h.* 
            FROM habitaciones h
            WHERE h.disponible = 1
            ORDER BY h.precio_noche
            """
        )
        
        habitaciones = cursor.fetchall()
        conn.close()
        
        return habitaciones
        
    except Exception as e:
        print(f"❌ Error obteniendo habitaciones: {str(e)}")
        return []

def fetch_servicios():
    """Obtener servicios disponibles"""
    try:
        conn = connect_db()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute(
            "SELECT * FROM servicios WHERE disponible = 1 ORDER BY nombre"
        )
        
        servicios = cursor.fetchall()
        conn.close()
        
        return servicios
        
    except Exception as e:
        print(f"❌ Error obteniendo servicios: {str(e)}")
        return []

def check_habitacion_disponible(habitacion_id, fecha_entrada, fecha_salida):
    """Verificar si una habitación está disponible en ciertas fechas"""
    try:
        conn = connect_db()
        cursor = conn.cursor()
        
        cursor.execute(
            """
            SELECT COUNT(*) 
            FROM reservas 
            WHERE habitacion_id = %s 
            AND estado = 'confirmada'
            AND (
                (fecha_entrada BETWEEN %s AND %s)
                OR (fecha_salida BETWEEN %s AND %s)
                OR (%s BETWEEN fecha_entrada AND fecha_salida)
                OR (%s BETWEEN fecha_entrada AND fecha_salida)
            )
            """,
            (habitacion_id, fecha_entrada, fecha_salida, 
             fecha_entrada, fecha_salida, fecha_entrada, fecha_salida)
        )
        
        count = cursor.fetchone()[0]
        conn.close()
        
        return count == 0
        
    except Exception as e:
        print(f"❌ Error verificando disponibilidad: {str(e)}")
        return False

# === RESERVAS ===
def create_reserva(cliente_id, habitacion_id, fecha_entrada, fecha_salida, noches, precio_total, adultos=1, ninos=0):
    """Crear una nueva reserva"""
    try:
        conn = connect_db()
        cursor = conn.cursor()
        
        # Verificar disponibilidad
        if not check_habitacion_disponible(habitacion_id, fecha_entrada, fecha_salida):
            return None
        
        # Crear reserva
        cursor.execute(
            """
            INSERT INTO reservas (
                cliente_id, habitacion_id, fecha_entrada, fecha_salida,
                noches, adultos, ninos, precio_total, estado, created_at, updated_at
            )
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, 'pendiente', NOW(), NOW())
            """,
            (cliente_id, habitacion_id, fecha_entrada, fecha_salida,
             noches, adultos, ninos, precio_total)
        )
        
        reserva_id = cursor.lastrowid
        
        conn.commit()
        conn.close()
        
        return reserva_id
        
    except Exception as e:
        print(f"❌ Error creando reserva: {str(e)}")
        return None

# === VENTAS Y PAGOS ===
def create_venta(cliente_id, monto_total, estado='completada'):
    """Crear una venta"""
    # try:
    conn = connect_db()
    cursor = conn.cursor()
    
    cursor.execute(
        """
        INSERT INTO ventas (
            cliente_id, monto_total, estado, 
            facturada, created_at, updated_at
        )
        VALUES (%s, %s, %s, 0, NOW(), NOW())
        """,
        (cliente_id, monto_total, estado)
    )
    
    venta_id = cursor.lastrowid
    
    # Actualizar reserva con venta_id
    # cursor.execute(
    #     "UPDATE reservas SET venta_id = %s, estado = 'confirmada' WHERE id = %s",
    #     (venta_id, reserva_id)
    # )
    
    conn.commit()
    conn.close()
    
    return venta_id
        
    # except Exception as e:
    #     print(f"❌ Error creando venta: {str(e)}")
    #     return None

def create_pago(venta_id, metodo_pago, monto, estado, referencia, detalles=None):
    """Crear un pago"""
    try:
        conn = connect_db()
        cursor = conn.cursor()
        
        detalles_json = None
        if detalles:
            import json
            detalles_json = json.dumps(detalles)
        
        cursor.execute(
            """
            INSERT INTO pagos (
                venta_id, metodo_pago, monto, estado, referencia,
                detalles, created_at, updated_at
            )
            VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
            """,
            (venta_id, metodo_pago, monto, estado, referencia, detalles_json)
        )
        
        pago_id = cursor.lastrowid
        
        conn.commit()
        conn.close()
        
        return pago_id
        
    except Exception as e:
        print(f"❌ Error creando pago: {str(e)}")
        return None

# === SESIONES ===
def get_user_session(user_id):
    """Obtener sesión de usuario desde DB (para persistencia)"""
    try:
        conn = connect_db()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute(
            """
            SELECT data FROM chat_sessions 
            WHERE user_id = %s ORDER BY updated_at DESC LIMIT 1
            """,
            (user_id,)
        )
        
        result = cursor.fetchone()
        conn.close()
        
        if result:
            import json
            return json.loads(result['data'])
        return None
        
    except:
        # Si no existe la tabla o hay error, retornar None
        return None

def update_session(user_id, session_data):
    """Actualizar sesión en DB"""
    try:
        conn = connect_db()
        cursor = conn.cursor()
        
        import json
        data_json = json.dumps(session_data)
        
        cursor.execute(
            """
            INSERT INTO chat_sessions (user_id, data, updated_at)
            VALUES (%s, %s, NOW())
            ON DUPLICATE KEY UPDATE 
            data = VALUES(data), updated_at = NOW()
            """,
            (user_id, data_json)
        )
        
        conn.commit()
        conn.close()
        return True
        
    except Exception as e:
        print(f"❌ Error actualizando sesión: {str(e)}")
        return False