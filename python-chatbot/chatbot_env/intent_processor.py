import torch
from transformers import AutoTokenizer, AutoModel
import re

print("Cargando modelo para análisis de intención...")
tokenizer = AutoTokenizer.from_pretrained("distilbert-base-uncased")
model = AutoModel.from_pretrained("distilbert-base-uncased")
model.eval()

# Frases de referencia
reference_sentences = {
    "habitaciones": "habitaciones disponibles tipo precio capacidad reservar alojamiento cuarto suite",
    "servicios": "servicios spa gimnasio desayuno wifi piscina restaurante amenities adicionales",
    "reserva": "reservar hacer reserva reserva quiero reservar alojarme hospedarme",
    "pago": "pagar pagar reserva tarjeta crédito débito qr transferencia pago",
    "registro": "registrarme crear cuenta nuevo usuario registrarse inicio sesión login"
}

# Precomputar embeddings
with torch.no_grad():
    ref_embeddings = {}
    for intent, text in reference_sentences.items():
        inputs = tokenizer(text, return_tensors="pt", truncation=True, padding=True)
        ref_embeddings[intent] = model(**inputs).last_hidden_state.mean(dim=1)

def get_embedding(text):
    """Obtener embedding del texto"""
    inputs = tokenizer(text, return_tensors="pt", truncation=True, padding=True, max_length=64)
    with torch.no_grad():
        outputs = model(**inputs)
        embedding = outputs.last_hidden_state.mean(dim=1)
    return embedding

def cosine_similarity(a, b):
    """Calcular similitud coseno"""
    return torch.nn.functional.cosine_similarity(a, b).item()

def detect_intent(user_input):
    """Detectar intención del usuario"""
    user_input_lower = user_input.lower()
    
    # Primero chequeos por palabras clave específicas
    if any(word in user_input_lower for word in ['registrarme', 'crear cuenta', 'soy nuevo', 'nuevo usuario']):
        return ["registro"]
    
    if any(word in user_input_lower for word in ['iniciar sesión', 'login', 'loguearme', 'ingresar']):
        return ["login"]
    
    if any(word in user_input_lower for word in ['reservar', 'hacer reserva', 'quiero reservar', 'alojarme']):
        return ["reserva"]
    
    if any(word in user_input_lower for word in ['pagar', 'pago', 'tarjeta', 'qr', 'transferencia']):
        return ["pago"]
    
    # Si no hay palabras clave, usar el modelo
    emb = get_embedding(user_input_lower)
    
    similarities = {}
    for intent, ref_emb in ref_embeddings.items():
        similarities[intent] = cosine_similarity(emb, ref_emb)
    
    # Filtrar intenciones con similitud > 0.3
    detected = [intent for intent, sim in similarities.items() if sim > 0.3]
    
    if not detected:
        # Si no se detecta nada específico, buscar palabras clave generales
        if any(word in user_input_lower for word in ['habitación', 'habitaciones', 'cuarto', 'suite']):
            detected.append("habitaciones")
        elif any(word in user_input_lower for word in ['servicio', 'servicios', 'spa', 'gimnasio']):
            detected.append("servicios")
        else:
            detected.append("general")
    
    return detected

def extract_dates(text):
    """Extraer fechas del texto"""
    patterns = [
        r'(\d{4})-(\d{2})-(\d{2})',  # YYYY-MM-DD
        r'(\d{2})/(\d{2})/(\d{4})',  # DD/MM/YYYY
        r'(\d{1,2}) de (\w+) de (\d{4})',  # 25 de diciembre de 2024
    ]
    
    dates = []
    for pattern in patterns:
        matches = re.findall(pattern, text)
        for match in matches:
            if len(match) == 3:
                dates.append(match)
    
    return dates[:2]  # Retornar máximo 2 fechas