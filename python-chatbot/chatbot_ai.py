from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
import torch
from transformers import AutoTokenizer, AutoModel
import re
import random

app = Flask(__name__)
CORS(app)

# === CONFIGURACIÓN DE BASE DE DATOS ===
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'tec_externo',
    'password': '4i8fxYzH`ZJ-UE-D=L4.',
    'database': 'apphotel',
    'port': 3306
}

# === CARGAR MODELO PARA EMBEDDINGS (NO GENERATIVO) ===
print("Cargando modelo DistilBERT para análisis de intención...")
tokenizer = AutoTokenizer.from_pretrained("distilbert-base-uncased")
model = AutoModel.from_pretrained("distilbert-base-uncased")
model.eval()
print("Modelo cargado ✅")

# === Frases de referencia para comparar similitud ===
reference_sentences = {
    "habitaciones": "habitaciones disponibles tipo precio capacidad reservar alojamiento cuarto",
    "servicios": "servicios spa gimnasio desayuno wifi piscina restaurante amenities"
}

# Precomputar embeddings de referencia
with torch.no_grad():
    ref_inputs_hab = tokenizer(reference_sentences["habitaciones"], return_tensors="pt", truncation=True, padding=True)
    ref_emb_hab = model(**ref_inputs_hab).last_hidden_state.mean(dim=1)

    ref_inputs_serv = tokenizer(reference_sentences["servicios"], return_tensors="pt", truncation=True, padding=True)
    ref_emb_serv = model(**ref_inputs_serv).last_hidden_state.mean(dim=1)

def get_embedding(text):
    inputs = tokenizer(text, return_tensors="pt", truncation=True, padding=True, max_length=64)
    with torch.no_grad():
        outputs = model(**inputs)
        embedding = outputs.last_hidden_state.mean(dim=1)
    return embedding

def detect_intent(user_input):
    emb = get_embedding(user_input.lower())
    
    # Calcular similitud coseno
    def cosine_similarity(a, b):
        return torch.nn.functional.cosine_similarity(a, b).item()
    
    sim_hab = cosine_similarity(emb, ref_emb_hab)
    sim_serv = cosine_similarity(emb, ref_emb_serv)
    
    threshold = 0.3
    intents = []
    if sim_hab > threshold:
        intents.append("habitaciones")
    if sim_serv > threshold:
        intents.append("servicios")
    
    return intents if intents else ["ambos"]  # Por defecto, mostrar ambos

# === FUNCIONES DE BASE DE DATOS ===
def fetch_habitaciones():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT tipo, descripcion, capacidad, precio_noche, disponible FROM habitaciones")
    data = cursor.fetchall()
    conn.close()
    print("🔍 Habitaciones en BD (TODAS, no solo disponibles):")
    for row in data:
        print(row)
    # Filtrar solo disponibles
    disponibles = [h for h in data if h['disponible'] == 1]
    print(f"✅ Habitaciones disponibles: {len(disponibles)}")
    return disponibles

def fetch_servicios():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT nombre, descripcion, precio, disponible FROM servicios")
    data = cursor.fetchall()
    conn.close()
    print("🔍 Servicios en BD (TODOS):")
    for row in data:
        print(row)
    disponibles = [s for s in data if s['disponible'] == 1]
    print(f"✅ Servicios disponibles: {len(disponibles)}")
    return disponibles

def format_habitaciones(habs):    

    if not habs:
        return "No hay habitaciones disponibles en este momento."
    lines = []
    for h in habs:
        desc = f" ({h['descripcion']})" if h['descripcion'] else ""
        lines.append(f"• {h['tipo']}{desc} — Capacidad: {h['capacidad']}, Precio: ${h['precio_noche']:.2f}/noche")
    return "🏨 **Habitaciones disponibles:**\n" + "\n".join(lines)

def format_servicios(servs):
    if not servs:
        return "No hay servicios adicionales disponibles."
    lines = []
    for s in servs:
        desc = f" ({s['descripcion']})" if s['descripcion'] else ""
        precio = "Gratuito" if s['precio'] == 0 else f"${s['precio']:.2f}"
        lines.append(f"• {s['nombre']}{desc} — {precio}")
    return "🛎️ **Servicios disponibles:**\n" + "\n".join(lines)

# === GENERAR RESPUESTA USANDO EL MODELO (solo para intención) ===
import re

def generate_response(user_input):
    try:
        mensaje = user_input.strip()
        if not mensaje:
            return "Por favor, escribe tu pregunta."

        mensaje_lower = mensaje.lower()

        # === 1. Detectar AGRADECIMIENTOS ===
        agradecimientos = [
            r"\bgracias\b",
            r"\bgracioso\b",  # evita falso positivo, pero mejor usar límites de palabra
            r"\bagradezco\b",
            r"\bthank you\b",
            r"\bthanks\b",
            r"\bty\b"
        ]
        
        es_agradecimiento = any(re.search(patron, mensaje_lower) for patron in agradecimientos)
        if es_agradecimiento:
            respuestas_gracias = [
                "¡Muchas gracias a usted! Le esperamos pronto en el Hotel ICI. 🌟",
                "A usted, estimado cliente. Estaremos encantados de recibir su reserva. 😊",
                "El placer es nuestro. ¡No dude en reservar cuando guste!",
                "Gracias a usted por considerar el Hotel ICI. ¡Le esperamos con los brazos abiertos!",
                "Nos alegra haberle ayudado. ¡Que tenga un excelente día y esperamos verle pronto!",
                "Agradecemos su confianza. Estaremos gustosos de atenderle en su próxima estancia.",
                "¡Mil gracias! Su visita sería un honor para nosotros. 🏨",
                "El Hotel ICI siempre estará a su disposición. ¡Gracias y que tenga un lindo día!"
            ]
            return random.choice(respuestas_gracias)

        # === 2. Detectar SALUDOS ===
        saludos = [
            r"\bhola\b",
            r"\bbuenos días\b",
            r"\bbuenas tardes\b",
            r"\bbuenas noches\b",
            r"\bbuen día\b",
            r"\bbuena tarde\b",
            r"\bbuena noche\b",
            r"\bsaludos\b",
            r"\bhey\b",
            r"\bhi\b",
            r"\bhello\b"
        ]
        
        es_saludo = any(re.search(patron, mensaje_lower) for patron in saludos)
        if es_saludo:
            return (
                "¡Hola! 👋 Bienvenido(a) al Hotel ICI. \n\n"
                "Mi nombre es ICI Assistant y estaré encantado de ayudarte. \n\n"
                "¿En qué puedo ayudarte hoy? Puedo darte información sobre:\n"
                "• Habitaciones disponibles\n"
                "• Servicios del hotel\n\n"
                "¡Que tengas un excelente día! 😊"
            )

        # === 3. Si no es saludo ni agradecimiento, usar el modelo para detectar intención ===
        intents = detect_intent(mensaje)
        
        parts = []
        if "habitaciones" in intents or "ambos" in intents:
            habs = fetch_habitaciones()
            parts.append(format_habitaciones(habs))
        if "servicios" in intents or "ambos" in intents:
            servs = fetch_servicios()
            parts.append(format_servicios(servs))
        
        if not parts:
            return "Lo siento, no entendí tu pregunta. ¿Podrías preguntar sobre habitaciones o servicios del hotel?"
        
        return "\n\n".join(parts)
    
    except Exception as e:
        print(f"Error en generate_response: {e}")
        return "Lo siento, tuve un pequeño problema. ¿Podrías repetir tu pregunta?"

# === RUTA DEL CHAT ===
@app.route('/chat', methods=['POST'])
def chat():
    try:
        data = request.get_json()
        msg = data.get('message', '').strip()
        if not msg:
            return jsonify({'reply': 'Por favor, escribe tu pregunta.'})
        
        reply = generate_response(msg)
        return jsonify({'reply': reply})
    except Exception as e:
        print(f"Error en /chat: {e}")
        return jsonify({'reply': 'El asistente no está disponible temporalmente.'}), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=8001, debug=False)



##Esto funciona pero responde incoherencia
# from flask import Flask, request, jsonify
# from flask_cors import CORS  # 👈 Importa CORS
# import mysql.connector
# import torch
# from transformers import AutoTokenizer, AutoModelForCausalLM

# app = Flask(__name__)
# CORS(app)  #

# # === CONFIGURACIÓN DE LA BASE DE DATOS (misma que Laravel) ===
# DB_CONFIG = {
#     'host': '127.0.0.1',
#     'user': 'tec_externo',              # ← ¡Cambia esto!
#     'password': '4i8fxYzH`ZJ-UE-D=L4.',              # ← ¡Cambia esto!
#     'database': 'apphotel',      # ← Nombre de tu BD
#     'port': 3306
# }

# # === CARGAR MODELO DE IA (una sola vez al iniciar) ===
# print("Cargando modelo DialoGPT-small...")
# tokenizer = AutoTokenizer.from_pretrained("microsoft/DialoGPT-small")
# model = AutoModelForCausalLM.from_pretrained("microsoft/DialoGPT-small")
# model.eval()  # Modo inferencia
# print("Modelo cargado ✅")

# def get_db_connection():
#     return mysql.connector.connect(**DB_CONFIG)

# def fetch_hotel_data():
#     """Obtiene datos reales de habitaciones y servicios"""
#     conn = get_db_connection()
#     cursor = conn.cursor(dictionary=True)
    
#     # Habitaciones disponibles
#     cursor.execute("SELECT tipo, capacidad, precio_noche FROM habitaciones WHERE disponible = 1")
#     habitaciones = cursor.fetchall()
    
#     # Servicios disponibles
#     cursor.execute("SELECT nombre, precio FROM servicios WHERE disponible = 1")
#     servicios = cursor.fetchall()
    
#     conn.close()
    
#     for x in habitaciones:
#       print(x)
    
#     for y in servicios:
#       print(y)

#     # Formatear como texto para el prompt
#     hab_text = "Habitaciones: " + "; ".join([
#         f"{h['tipo']} (capacidad: {h['capacidad']}, ${h['precio_noche']}/noche)"
#         for h in habitaciones
#     ]) if habitaciones else "No hay habitaciones disponibles."
    
#     serv_text = "Servicios: " + "; ".join([
#         f"{s['nombre']} (${s['precio']})" if s['precio'] > 0 else f"{s['nombre']} (gratuito)"
#         for s in servicios
#     ]) if servicios else "No hay servicios adicionales."
    
#     return f"{hab_text} | {serv_text}"

# def generate_response(user_input):
#     try:
#         # Obtener datos reales del hotel
#         hotel_data = fetch_hotel_data()
#         # print(hotel_data)
#         # Crear un prompt enriquecido con contexto real
#         prompt = (
#             f"Eres un asistente virtual amable y profesional del Hotel ICI. "
#             f"Responde SOLO con la información proporcionada. "
#             f"Datos actuales del hotel: {hotel_data}. "
#             f"Pregunta del cliente: '{user_input}'. "
#             f"Respuesta:"
#         )
        
#         # Tokenizar y generar
#         inputs = tokenizer.encode(prompt + tokenizer.eos_token, return_tensors="pt")
        
#         # Generar respuesta (con límites para evitar bucles)
#         outputs = model.generate(
#             inputs,
#             max_length=200,
#             pad_token_id=tokenizer.eos_token_id,
#             temperature=0.7,
#             top_p=0.9,
#             do_sample=True,
#             num_return_sequences=1
#         )
        
#         response = tokenizer.decode(outputs[0], skip_special_tokens=True)
        
#         # Extraer solo la parte de la respuesta (después de "Respuesta:")
#         if "Respuesta:" in response:
#             response = response.split("Respuesta:")[-1].strip()
        
#         # Fallback si la respuesta es muy larga o rara
#         if len(response) > 300:
#             response = "Gracias por tu consulta. Tenemos habitaciones y servicios disponibles. ¿Te gustaría más detalles?"
            
#         return response if response else "Lo siento, no entendí tu pregunta."
        
#     except Exception as e:
#         print(f"Error en IA: {e}")
#         return "Lo siento, hubo un problema técnico. ¿Puedes repetir tu pregunta?"

# @app.route('/chat', methods=['POST'])
# def chat():
#     try:
#         data = request.get_json()
#         user_message = data.get('message', '').strip()
        
#         if not user_message:
#             return jsonify({'reply': 'Por favor, escribe una pregunta.'})
        
#         # Generar respuesta con IA + datos reales
#         reply = generate_response(user_message)
#         return jsonify({'reply': reply})
        
#     except Exception as e:
#         print(f"Error en /chat: {e}")
#         return jsonify({'reply': 'El asistente no está disponible en este momento.'}), 500

# if __name__ == '__main__':
#     app.run(host='127.0.0.1', port=8001, debug=False)

