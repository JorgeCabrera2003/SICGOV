<!-- MAR-IA Chatbot UI -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/chatbot.css?v=<?= time() ?>">

<div class="chatbot-container">
    <div class="chatbot-window" id="mariaChatbot">
        <div class="chatbot-header">
            <div class="bot-info">
                <div class="bot-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h6 class="bot-name">MAR-IA</h6>
                </div>
            </div>
            <div class="chatbot-close" onclick="document.getElementById('mariaChatbot').classList.remove('active')">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chat-message message-bot">
                ¡Hola! Soy MAR-IA ✨, tu asistente inteligente experta en análisis de datos.
            </div>
            <div class="chat-message message-bot">
                ¿Qué información necesitas hoy? Puedes pedirme reportes personalizados como:
                <div class="chatbot-suggestions">
                    <span class="suggestion-chip" onclick="enviarSugerencia(this)">"Generar reporte de usuarios inactivos"</span>
                    <span class="suggestion-chip" onclick="enviarSugerencia(this)">"Resumen del inventario en PDF"</span>
                    <span class="suggestion-chip" onclick="enviarSugerencia(this)">"¿Cuáles mesas están libres?"</span>
                </div>
            </div>
        </div>
        <div class="chatbot-input-container">
            <div class="chatbot-input">
                <input type="text" id="chatbotInput" placeholder="Habla con MAR-IA..." onkeypress="handleEnter(event)">
                <button type="button" onclick="enviarMensaje()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
    <div class="chatbot-toggle" onclick="document.getElementById('mariaChatbot').classList.toggle('active')">
        <i class="fas fa-comment-dots"></i>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/chatbot.js?v=<?= time() ?>"></script>
