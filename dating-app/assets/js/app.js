/**
 * LatinMatch - JavaScript principal
 */

document.addEventListener('DOMContentLoaded', function () {

    // =============================================
    // Sistema de Discover (swipe)
    // =============================================
    const discoverCard = document.getElementById('discover-card');

    if (discoverCard) {
        initDiscoverSwipe();
    }

    function initDiscoverSwipe() {
        let startX = 0;
        let currentX = 0;
        let isDragging = false;

        discoverCard.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            discoverCard.style.transition = 'none';
        });

        discoverCard.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX - startX;
            const rotate = currentX * 0.1;
            discoverCard.style.transform = `translateX(${currentX}px) rotate(${rotate}deg)`;
        });

        discoverCard.addEventListener('touchend', () => {
            isDragging = false;
            discoverCard.style.transition = '0.3s ease';

            if (currentX > 100) {
                handleLike();
            } else if (currentX < -100) {
                handlePass();
            } else {
                discoverCard.style.transform = '';
            }
            currentX = 0;
        });
    }

    // Botones de like/pass
    window.handleLike = function () {
        const userId = document.getElementById('current-profile-id')?.value;
        if (!userId) return;

        const card = document.getElementById('discover-card');
        card.classList.add('swipe-right');

        fetch(getBaseUrl() + '/api/like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
            .then(r => r.json())
            .then(data => {
                if (data.is_match) {
                    showMatchNotification(data.user_name);
                }
                setTimeout(() => loadNextProfile(), 400);
            })
            .catch(() => {
                setTimeout(() => loadNextProfile(), 400);
            });
    };

    window.handlePass = function () {
        const card = document.getElementById('discover-card');
        card.classList.add('swipe-left');
        setTimeout(() => loadNextProfile(), 400);
    };

    function loadNextProfile() {
        window.location.reload();
    }

    function showMatchNotification(name) {
        const overlay = document.createElement('div');
        overlay.className = 'match-overlay';
        overlay.innerHTML = `
            <div class="match-popup fade-in">
                <div style="font-size: 4rem;">&#10084;</div>
                <h2>¡Es un Match!</h2>
                <p>Tú y <strong>${escapeHtml(name)}</strong> se gustan mutuamente</p>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: center;">
                    <a href="${getBaseUrl()}/pages/matches.php" class="btn btn-primary" style="width: auto;">Enviar mensaje</a>
                    <button class="btn btn-outline" onclick="this.closest('.match-overlay').remove()">Seguir viendo</button>
                </div>
            </div>
        `;
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:1000;display:flex;align-items:center;justify-content:center;';
        overlay.querySelector('.match-popup').style.cssText = 'background:white;padding:2rem;border-radius:16px;text-align:center;max-width:320px;';
        document.body.appendChild(overlay);
    }

    // =============================================
    // Chat
    // =============================================
    const chatForm = document.getElementById('chat-form');
    const chatMessages = document.getElementById('chat-messages');

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = this.querySelector('input[name="message"]');
            const matchId = this.querySelector('input[name="match_id"]').value;
            const message = input.value.trim();

            if (!message) return;

            // Agregar mensaje al chat inmediatamente
            appendMessage(message, true);
            input.value = '';

            fetch(getBaseUrl() + '/api/send-message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ match_id: matchId, message: message })
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.error || 'Error al enviar mensaje');
                    }
                })
                .catch(() => alert('Error de conexión'));
        });

        // Scroll al final
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Polling para nuevos mensajes (cada 5 segundos)
        setInterval(pollMessages, 5000);
    }

    function appendMessage(text, isSent) {
        if (!chatMessages) return;
        const div = document.createElement('div');
        div.className = 'message ' + (isSent ? 'message-sent' : 'message-received');
        div.textContent = text;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function pollMessages() {
        const matchId = document.querySelector('input[name="match_id"]')?.value;
        if (!matchId) return;

        fetch(getBaseUrl() + '/api/get-messages.php?match_id=' + matchId)
            .then(r => r.json())
            .then(data => {
                if (data.messages && chatMessages) {
                    // Reemplazar mensajes
                    chatMessages.innerHTML = '';
                    const currentUserId = document.getElementById('current-user-id')?.value;
                    data.messages.forEach(msg => {
                        const isSent = String(msg.sender_id) === String(currentUserId);
                        const div = document.createElement('div');
                        div.className = 'message ' + (isSent ? 'message-sent' : 'message-received');
                        div.textContent = msg.message;
                        chatMessages.appendChild(div);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            })
            .catch(() => { });
    }

    // =============================================
    // Upload de fotos
    // =============================================
    const photoUpload = document.getElementById('photo-upload');
    if (photoUpload) {
        photoUpload.addEventListener('change', function () {
            if (this.files.length === 0) return;

            const formData = new FormData();
            formData.append('photo', this.files[0]);

            fetch(getBaseUrl() + '/api/upload-photo.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error || 'Error al subir la foto');
                    }
                })
                .catch(() => alert('Error de conexión'));
        });
    }

    // =============================================
    // Utilidades
    // =============================================
    function getBaseUrl() {
        return document.querySelector('meta[name="base-url"]')?.content ||
            window.location.origin + '/dating-app';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
