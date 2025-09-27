<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>🏨 Hotel ICI - Tu Estadía Perfecta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
    }
    .hero-section {
      background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://placehold.co/1920x600/4a6fa5/white?text=Hotel+ICI+-+Lujo+y+Confort') no-repeat center center/cover;
      height: 600px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
    }
    .room-card {
      transition: transform 0.3s, box-shadow 0.3s;
    }
    .room-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .section-title {
      position: relative;
      margin-bottom: 2rem;
    }
    .section-title:after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: #4a6fa5;
    }
    footer {
      background: #2c3e50;
      color: white;
      padding: 2rem 0;
    }
    #chatbot {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 350px;
      max-height: 500px;
      background: white;
      border: 1px solid #ddd;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      display: none;
      flex-direction: column;
      z-index: 1050;
    }
    #chat-messages {
      padding: 15px;
      overflow-y: auto;
      flex-grow: 1;
      max-height: 350px;
    }
    .message { 
      padding: 8px 12px; 
      margin: 6px 0; 
      border-radius: 10px; 
      max-width: 80%; 
    }
    .user { 
      background: #e3f2fd; 
      margin-left: auto; 
    }
    .bot { 
      background: #f8f9fa; 
      border-left: 3px solid #4a6fa5;
    }
    #chat-input {
      display: flex;
      padding: 10px;
      border-top: 1px solid #eee;
    }
    #chat-input input {
      flex: 1;
      border: 1px solid #ccc;
      border-radius: 20px;
      padding: 8px 15px;
    }
    #chat-input button {
      margin-left: 10px;
      border-radius: 20px;
    }
    .btn-admin {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1040;
    }
  </style>
</head>
<body>

<!-- Botón de acceso admin (flotante) -->
<a href="{{ route('login') }}" class="btn btn-outline-dark btn-sm btn-admin">
  <i class="fas fa-lock"></i> Acceso Admin
</a>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#inicio">🏨 Hotel ICI</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="#habitaciones">Habitaciones</a></li>
        <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
        <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Banner -->
<section id="inicio" class="hero-section">
  <div class="container">
    <h1 class="display-4 fw-bold">Bienvenido al Hotel ICI</h1>
    <p class="lead">Lujo, confort y atención personalizada en el corazón de la ciudad.</p>
    <button id="openChat" class="btn btn-success btn-lg mt-3">
      <i class="fas fa-robot"></i> Habla con nuestro asistente
    </button>
  </div>
</section>

<!-- Habitaciones -->
<section id="habitaciones" class="py-5 bg-light">
  <div class="container">
    <h2 class="section-title text-center">Nuestras Habitaciones</h2>
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card room-card h-100">
          <img src="https://placehold.co/600x400/e0e0e0/4a6fa5?text=Habitación+Doble" class="card-img-top" alt="Doble">
          <div class="card-body">
            <h5 class="card-title">Doble Matrimonial</h5>
            <p class="card-text">Ideal para parejas. Incluye desayuno, Wi-Fi y estacionamiento.</p>
            <p class="text-primary fw-bold">Desde $80/noche</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card room-card h-100">
          <img src="https://placehold.co/600x400/e0e0e0/4a6fa5?text=Suite+Presidencial" class="card-img-top" alt="Suite">
          <div class="card-body">
            <h5 class="card-title">Suite Presidencial</h5>
            <p class="card-text">Lujo extremo con jacuzzi, vista panorámica y servicio 24h.</p>
            <p class="text-primary fw-bold">Desde $150/noche</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card room-card h-100">
          <img src="https://placehold.co/600x400/e0e0e0/4a6fa5?text=Familiar" class="card-img-top" alt="Familiar">
          <div class="card-body">
            <h5 class="card-title">Familiar</h5>
            <p class="card-text">Espaciosa para hasta 4 personas. Perfecta para familias.</p>
            <p class="text-primary fw-bold">Desde $120/noche</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Servicios -->
<section id="servicios" class="py-5">
  <div class="container">
    <h2 class="section-title text-center">Servicios Incluidos</h2>
    <div class="row text-center">
      <div class="col-md-3 mb-4">
        <div class="p-3">
          <i class="fas fa-utensils fa-2x text-primary mb-3"></i>
          <h5>Desayuno Buffet</h5>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="p-3">
          <i class="fas fa-wifi fa-2x text-primary mb-3"></i>
          <h5>Wi-Fi Gratuito</h5>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="p-3">
          <i class="fas fa-parking fa-2x text-primary mb-3"></i>
          <h5>Estacionamiento</h5>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="p-3">
          <i class="fas fa-concierge-bell fa-2x text-primary mb-3"></i>
          <h5>Recepción 24h</h5>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contacto -->
<section id="contacto" class="py-5 bg-light">
  <div class="container">
    <h2 class="section-title text-center">Contáctanos</h2>
    <div class="row justify-content-center">
      <div class="col-md-8 text-center">
        <p><i class="fas fa-map-marker-alt"></i> Av. Siempre Viva 123, Ciudad</p>
        <p><i class="fas fa-phone"></i> +56 9 1234 5678</p>
        <p><i class="fas fa-envelope"></i> contacto@hotelici.com</p>
        <button id="openChat2" class="btn btn-outline-primary mt-3">
          <i class="fas fa-comments"></i> Pregúntanos por el chat
        </button>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer>
  <div class="container text-center">
    <p>&copy; 2025 Hotel ICI. Todos los derechos reservados.</p>
    <p>Diseñado para el Trabajo Final de Curso - ICI</p>
  </div>
</footer>

<!-- Chatbot flotante -->
<div id="chatbot">
  <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
    <strong><i class="fas fa-robot me-2"></i>Asistente Virtual</strong>
    <button id="closeChat" class="btn btn-sm btn-light">&times;</button>
  </div>
  <div id="chat-messages">
    <div class="message bot">¡Hola! 👋 Soy tu asistente virtual del Hotel ICI. ¿En qué puedo ayudarte?</div>
  </div>
  <div id="chat-input">
    <input type="text" id="user-input" placeholder="Escribe tu pregunta..." autocomplete="off">
    <button id="send-btn" class="btn btn-primary">Enviar</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const openChat = document.getElementById('openChat');
  const openChat2 = document.getElementById('openChat2');
  const closeChat = document.getElementById('closeChat');
  const chatbot = document.getElementById('chatbot');
  const chatMessages = document.getElementById('chat-messages');
  const userInput = document.getElementById('user-input');
  const sendBtn = document.getElementById('send-btn');

  [openChat, openChat2].forEach(btn => {
    btn.addEventListener('click', () => {
      chatbot.style.display = 'flex';
      userInput.focus();
    });
  });
  closeChat.addEventListener('click', () => chatbot.style.display = 'none');

  function addMessage(text, sender) {
    const div = document.createElement('div');
    div.classList.add('message', sender);
    div.textContent = text;
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function getBotResponse(userMsg) {
    const msg = userMsg.toLowerCase();
    if (msg.includes('doble') || msg.includes('matrimonial')) {
      return "¡Buenas tardes! Tenemos habitaciones dobles matrimoniales disponibles desde $80/noche, incluyen desayuno buffet, Wi-Fi y estacionamiento.";
    } else if (msg.includes('suite')) {
      return "Contamos con suites de lujo desde $150/noche, con jacuzzi, vista panorámica y servicio de habitaciones 24h.";
    } else if (msg.includes('familiar')) {
      return "Nuestras habitaciones familiares alojan hasta 4 personas y cuestan desde $120/noche.";
    } else if (msg.includes('precio') || msg.includes('cuánto')) {
      return "Nuestros precios van desde $80 (doble) hasta $150 (suite). ¿Le gustaría reservar?";
    } else if (msg.includes('hola') || msg.includes('buenos') || msg.includes('buenas')) {
      return "¡Hola! ¿En qué puedo ayudarle hoy?";
    } else if (msg.includes('servicio') || msg.includes('incluye')) {
      return "Incluimos desayuno buffet, Wi-Fi gratuito, estacionamiento y recepción 24 horas.";
    } else {
      return "Lo siento, solo puedo ayudarle con información sobre habitaciones, precios y servicios. ¿Tal vez preguntar por 'habitaciones dobles' o 'suite'?";
    }
  }

  function sendMessage() {
    const msg = userInput.value.trim();
    if (!msg) return;
    addMessage(msg, 'user');
    userInput.value = '';
    setTimeout(() => {
      const response = getBotResponse(msg);
      addMessage(response, 'bot');
    }, 500);
  }

  sendBtn.addEventListener('click', sendMessage);
  userInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
  });
</script>

</body>
</html>