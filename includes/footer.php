    </main><!-- /content -->
</div><!-- /main -->

<!-- ═══════════════════════════════════════
     NUTRIBOT — ASISTENTE VIRTUAL FLOTANTE
     ═══════════════════════════════════════ -->

<!-- Botón flotante -->
<button id="nutribot-fab" title="NutriBot — Asistente Virtual" onclick="toggleNutribot()">
    🤖
    <span class="fab-badge" id="nb-badge" style="display:none">1</span>
</button>

<!-- Ventana del chat -->
<div id="nutribot-window">
    <div class="nb-header">
        <div class="nb-avatar">🥗</div>
        <div class="nb-header-info">
            <div class="nb-name">NutriBot</div>
            <div class="nb-status"><span class="nb-online-dot"></span> Asistente Nutricional IA</div>
        </div>
        <button class="nb-close" onclick="toggleNutribot()" title="Cerrar">✕</button>
    </div>

    <div class="nb-messages" id="nb-messages">
        <div class="nb-msg bot">
            <div class="nb-msg-icon">🥗</div>
            <div class="nb-bubble">¡Hola! Soy <strong>NutriBot</strong> 👋, tu asistente de NutriPredict. Puedo ayudarte a entender los datos del sistema, resolver dudas sobre módulos o darte recomendaciones nutricionales.<br><br>¿En qué te puedo ayudar hoy?</div>
        </div>
    </div>

    <div class="nb-suggestions" id="nb-suggestions">
        <button class="nb-chip" onclick="sendSuggestion('¿Cuántos estudiantes están en riesgo alto?')">🔴 Riesgo alto</button>
        <button class="nb-chip" onclick="sendSuggestion('¿Cómo funciona el análisis predictivo?')">🧠 Predictivo</button>
        <button class="nb-chip" onclick="sendSuggestion('¿Cuántas alertas activas hay?')">🔔 Alertas</button>
        <button class="nb-chip" onclick="sendSuggestion('Dame recomendaciones para mejorar la nutrición escolar')">🥗 Nutrición</button>
    </div>

    <div class="nb-input-area">
        <textarea id="nb-input" placeholder="Escribe tu consulta…" rows="1" onkeydown="nbKeydown(event)"></textarea>
        <button id="nb-send" onclick="sendNutribot()" title="Enviar">➤</button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="js/main.js"></script>

<script>
const nbWindow  = document.getElementById('nutribot-window');
const nbMsgs    = document.getElementById('nb-messages');
const nbInput   = document.getElementById('nb-input');
const nbSend    = document.getElementById('nb-send');
const nbBadge   = document.getElementById('nb-badge');
const nbSugg    = document.getElementById('nb-suggestions');

let chatHistory = [];
let botOpen = false;
let firstOpen = true;

function toggleNutribot() {
    botOpen = !botOpen;
    if (botOpen) {
        nbWindow.classList.add('open');
        nbBadge.style.display = 'none';
        setTimeout(() => nbInput.focus(), 200);
    } else {
        nbWindow.classList.remove('open');
    }
}

function nbKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendNutribot(); }
}

nbInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 80) + 'px';
});

function sendSuggestion(text) {
    nbSugg.style.display = 'none';
    nbInput.value = text;
    sendNutribot();
}

async function sendNutribot() {
    const msg = nbInput.value.trim();
    if (!msg) return;
    nbSugg.style.display = 'none';
    appendMsg('user', msg);
    chatHistory.push({ role: 'user', content: msg });
    nbInput.value = '';
    nbInput.style.height = 'auto';
    nbSend.disabled = true;
    const typingId = appendTyping();
    try {
        const res = await fetch('nutribot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mensaje: msg, historial: chatHistory.slice(-8) })
        });
        removeTyping(typingId);
        if (!res.ok) throw new Error('Error');
        const data = await res.json();
        const respuesta = data.respuesta || 'Lo siento, ocurrió un error.';
        appendMsg('bot', respuesta);
        chatHistory.push({ role: 'assistant', content: respuesta });
        if (!botOpen) nbBadge.style.display = 'flex';
    } catch(e) {
        removeTyping(typingId);
        appendMsg('bot', '⚠️ No pude conectar con el servidor. Verifica la conexión e inténtalo de nuevo.');
    }
    nbSend.disabled = false;
    nbInput.focus();
}

function appendMsg(role, text) {
    const isBot = role === 'bot';
    const div = document.createElement('div');
    div.className = 'nb-msg ' + role;
    const html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
    div.innerHTML = `<div class="nb-msg-icon">${isBot ? '🥗' : '👤'}</div><div class="nb-bubble">${html}</div>`;
    nbMsgs.appendChild(div);
    nbMsgs.scrollTop = nbMsgs.scrollHeight;
}

function appendTyping() {
    const id = 'typing_' + Date.now();
    const div = document.createElement('div');
    div.className = 'nb-msg bot'; div.id = id;
    div.innerHTML = `<div class="nb-msg-icon">🥗</div><div class="nb-bubble" style="padding:8px 13px"><div class="nb-typing"><span></span><span></span><span></span></div></div>`;
    nbMsgs.appendChild(div);
    nbMsgs.scrollTop = nbMsgs.scrollHeight;
    return id;
}

function removeTyping(id) { const el = document.getElementById(id); if (el) el.remove(); }

// Dark Mode
function initDarkMode() {
    if (localStorage.getItem('nutripredict_dark') === '1') document.body.classList.add('dark-mode');
    updateDarkBtn();
}
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('nutripredict_dark', document.body.classList.contains('dark-mode') ? '1' : '0');
    updateDarkBtn();
}
function updateDarkBtn() {
    const btn = document.getElementById('btn-dark-mode');
    if (!btn) return;
    const d = document.body.classList.contains('dark-mode');
    btn.textContent = d ? '☀️' : '🌙';
    btn.title = d ? 'Modo claro' : 'Modo oscuro';
}
initDarkMode();
</script>
</body>
</html>
