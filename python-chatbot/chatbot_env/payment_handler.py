import uuid
import random
from datetime import datetime, timedelta

def generate_qr_payment(reserva_id, monto):
    """Generar datos para pago QR (simulación)"""
    payment_id = f"QR-{reserva_id:06d}-{int(datetime.now().timestamp())}"
    
    # En producción, esto generaría un QR real con una URL de pago
    return {
        'payment_id': payment_id,
        'amount': monto,
        'currency': 'CLP',
        'description': f'Reserva #{reserva_id:06d} - Hotel ICI',
        'expiration': (datetime.now() + timedelta(minutes=30)).isoformat(),
        'payment_url': f"https://payment.hotelici.com/pay/{payment_id}",
        'qr_data': f"hotelici://pay/{payment_id}/{monto}",
        'status': 'pending'
    }

def verify_payment_status(payment_id):
    """Verificar estado de pago QR (simulación)"""
    # En producción, esto consultaría el gateway de pagos
    
    # Simular verificación (80% de éxito para demo)
    if random.random() < 0.8:
        return {
            'success': True,
            'status': 'completed',
            'transaction_id': f"TXN-{int(datetime.now().timestamp())}",
            'verified_at': datetime.now().isoformat()
        }
    else:
        return {
            'success': False,
            'status': 'pending',
            'message': 'Pago no encontrado o aún pendiente'
        }

def process_credit_card(card_number, card_holder, expiration, cvv, amount):
    """Procesar pago con tarjeta (simulación)"""
    # En producción, usar un gateway como Stripe, MercadoPago, etc.
    
    # Validaciones básicas (demo)
    if not card_number or len(str(card_number).replace(' ', '')) < 13:
        return {'success': False, 'error': 'Número de tarjeta inválido'}
    
    if not expiration or '/' not in expiration:
        return {'success': False, 'error': 'Fecha de expiración inválida (MM/AA)'}
    
    if not cvv or len(str(cvv)) not in [3, 4]:
        return {'success': False, 'error': 'CVV inválido'}
    
    # Simular procesamiento (90% éxito para demo)
    if random.random() < 0.9:
        return {
            'success': True,
            'transaction_id': f"CC-{int(datetime.now().timestamp())}",
            'authorization_code': f"AUTH-{random.randint(100000, 999999)}",
            'amount_charged': amount,
            'currency': 'CLP',
            'processed_at': datetime.now().isoformat()
        }
    else:
        return {
            'success': False,
            'error': 'Pago rechazado por el banco',
            'decline_reason': 'Fondos insuficientes'
        }

def generate_payment_reference(prefix="PAY"):
    """Generar referencia única para pago"""
    timestamp = int(datetime.now().timestamp())
    random_part = random.randint(1000, 9999)
    return f"{prefix}-{timestamp}-{random_part}"