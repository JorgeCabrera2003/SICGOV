function scrollToBottom() {
    const messages = document.getElementById('chatbotMessages');
    messages.scrollTop = messages.scrollHeight;
}

function appendMessage(text, type) {
    const messages = document.getElementById('chatbotMessages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'chat-message ' + type;
    msgDiv.textContent = text;
    messages.appendChild(msgDiv);
    scrollToBottom();
}

function enviarMensaje() {
    const input = document.getElementById('chatbotInput');
    const text = input.value.trim();
    
    if(text === '') return;
    
    // Agregar mensaje del usuario
    appendMessage(text, 'message-user');
    input.value = '';
    
    // Simular respuesta del bot
    setTimeout(() => {
        appendMessage("Estoy analizando tu solicitud... Sin embargo, sigo en fase de entrenamiento y esta funcionalidad estará disponible próximamente. ¡Vuelve pronto!", 'message-bot');
    }, 1000);
}

function enviarSugerencia(chip) {
    const text = chip.textContent.replace(/"/g, '');
    document.getElementById('chatbotInput').value = text;
    enviarMensaje();
}

function handleEnter(e) {
    if(e.key === 'Enter') {
        enviarMensaje();
    }
}
