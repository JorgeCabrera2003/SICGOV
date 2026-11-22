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
    
    // Mostrar mensaje de "escribiendo..."
    const messages = document.getElementById('chatbotMessages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'chat-message message-bot typing-indicator';
    typingDiv.id = 'mariaTyping';
    typingDiv.innerHTML = 'MAR-IA está procesando... <i class="fas fa-circle-notch fa-spin"></i>';
    messages.appendChild(typingDiv);
    scrollToBottom();

    // Enviar petición al backend PHP
    const formData = new FormData();
    formData.append('peticion', 'mar_ia');
    formData.append('mensaje', text);

    fetch('?page=Reporte&type=reportes', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text);
            throw new Error('Invalid JSON');
        }
    })
    .then(data => {
        // Remover el indicador de "escribiendo"
        const typingElement = document.getElementById('mariaTyping');
        if (typingElement) typingElement.remove();

        if (data.status === 'success') {
            let replyText = data.message;
            if (data.pdf_url) {
                // Crear contenedor para mensaje + botón
                const container = document.createElement('div');
                container.innerHTML = `
                    <p style="margin-bottom: 8px;">${replyText}</p>
                    <a href="${data.pdf_url}" target="_blank" class="btn btn-sm btn-primary" style="display: inline-block; text-decoration: none; font-weight: bold; padding: 6px 12px; border-radius: 4px; background-color: #fd7e14; border-color: #fd7e14; color: white;">
                        <i class="fas fa-file-pdf"></i> Abrir Reporte PDF
                    </a>
                `;
                appendHtmlMessage(container, 'message-bot');
            } else {
                appendMessage(replyText, 'message-bot');
            }
        } else {
            appendMessage(data.message || 'Ocurrió un error inesperado. Intenta de nuevo.', 'message-bot');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const typingElement = document.getElementById('mariaTyping');
        if (typingElement) typingElement.remove();
        appendMessage('Lo siento, hubo un problema al conectarse con el servidor.', 'message-bot');
    });
}

function appendHtmlMessage(htmlElement, type) {
    const messages = document.getElementById('chatbotMessages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'chat-message ' + type;
    msgDiv.appendChild(htmlElement);
    messages.appendChild(msgDiv);
    scrollToBottom();
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
