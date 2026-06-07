// js/main.js — NutriPredict Escolar
document.addEventListener('DOMContentLoaded', function () {

    // ── Animación barras de cobertura ──
    document.querySelectorAll('.score-bar-fill').forEach(function (b) {
        const w = b.style.width;
        b.style.width = '0%';
        setTimeout(() => b.style.width = w, 200);
    });

    // ── Buscador global en tablas ──
    const gs = document.getElementById('globalSearch');
    if (gs) {
        gs.addEventListener('input', function () {
            const f = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().includes(f) ? '' : 'none';
            });
        });
    }

    // ── Modales ──
    document.querySelectorAll('[data-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.modal;
            const m  = document.getElementById(id);
            if (m) m.classList.add('show');
        });
    });
    document.querySelectorAll('.modal-close, .modal-overlay').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (e.target === el) {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('show'));
            }
        });
    });
    document.querySelectorAll('.modal').forEach(function(m){
        m.addEventListener('click', e => e.stopPropagation());
    });

    // ── Auto-ocultar alertas flash ──
    const flash = document.querySelector('.alert-box');
    if (flash) setTimeout(() => flash.style.display = 'none', 4000);

    // ── Confirmar eliminación ──
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || '¿Estás seguro?')) e.preventDefault();
        });
    });
});

// ── Función global para gráfica de barras ──
function initBarChart(canvasId, labels, datasets) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#0f172a', titleColor: '#fff', bodyColor: 'rgba(255,255,255,.75)', padding: 10, cornerRadius: 8 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#64748b' } },
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 5 }, beginAtZero: true }
            }
        }
    });
}

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 3600) return 'Hace ' + Math.floor(diff / 60) + ' min';
    if (diff < 86400) return 'Hace ' + Math.floor(diff / 3600) + ' h';
    return 'Hace ' + Math.floor(diff / 86400) + ' día(s)';
}
