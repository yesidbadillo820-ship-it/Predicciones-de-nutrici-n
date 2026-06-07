<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriPredict Escolar — Nutrición Inteligente</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root { --verde:#22c55e; --verde-dark:#16a34a; --verde-light:#dcfce7; --azul-dark:#0f172a; --gris:#64748b; }
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;color:var(--azul-dark);background:#fff}

.navbar{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.96);backdrop-filter:blur(12px);border-bottom:1px solid #e2e8f0;padding:0 56px;height:64px;display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-icon{width:36px;height:36px;background:var(--verde);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px}
.nav-logo-text .name{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:15px;color:var(--azul-dark);line-height:1.1}
.nav-logo-text .sub{font-size:10px;color:var(--gris)}
.nav-btn{background:var(--verde);color:#fff;border:none;padding:10px 22px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:.18s}
.nav-btn:hover{background:var(--verde-dark);transform:translateY(-1px)}

.hero{min-height:100vh;padding-top:64px;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:40px;padding-left:80px;padding-right:48px;background:linear-gradient(135deg,#f0fdf4 0%,#f8fafc 50%,#eff6ff 100%);position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;right:-100px;top:-100px;width:500px;height:500px;background:radial-gradient(circle,rgba(34,197,94,.12) 0%,transparent 70%);border-radius:50%}
.hero::after{content:'';position:absolute;bottom:-80px;left:200px;width:300px;height:300px;background:radial-gradient(circle,rgba(14,165,233,.08) 0%,transparent 70%);border-radius:50%}
.hero-content{position:relative;z-index:1}
.hero-badge{display:inline-flex;align-items:center;gap:7px;background:var(--verde-light);color:var(--verde-dark);font-size:12.5px;font-weight:600;padding:6px 14px;border-radius:20px;margin-bottom:24px}
.hero-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:52px;font-weight:800;color:var(--azul-dark);line-height:1.1;margin-bottom:12px}
.hero-title span{color:var(--verde);display:block}
.hero-desc{font-size:16px;color:var(--gris);line-height:1.7;margin-bottom:32px;max-width:480px}
.hero-btns{display:flex;gap:14px;margin-bottom:40px}
.btn-primary{background:var(--verde);color:#fff;padding:13px 28px;border-radius:12px;font-size:15px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:.18s}
.btn-primary:hover{background:var(--verde-dark);transform:translateY(-2px);box-shadow:0 8px 24px rgba(34,197,94,.3)}
.btn-outline{background:transparent;color:var(--verde-dark);padding:13px 28px;border-radius:12px;font-size:15px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;border:2px solid var(--verde);transition:.18s}
.btn-outline:hover{background:var(--verde-light);transform:translateY(-2px)}
.hero-stats{display:flex;gap:32px}
.hero-stat .num{font-family:'Plus Jakarta Sans',sans-serif;font-size:28px;font-weight:800;color:var(--verde);line-height:1}
.hero-stat .lbl{font-size:12px;color:var(--gris);margin-top:2px}

.hero-visual{position:relative;z-index:1}
.hero-img-wrap{border-radius:20px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.14);position:relative}
.hero-img-wrap img{width:100%;display:block;height:420px;object-fit:cover}
.float-card{position:absolute;background:#fff;border-radius:14px;padding:12px 18px;box-shadow:0 8px 30px rgba(0,0,0,.12);display:flex;align-items:center;gap:10px;font-family:'Plus Jakarta Sans',sans-serif;animation:floatUp 3s ease-in-out infinite}
.float-card.tr{top:20px;right:-20px}
.float-card.bl{bottom:30px;left:-20px;animation-delay:.8s}
.float-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px}
.float-lbl{font-size:11px;color:var(--gris)}
.float-val{font-size:18px;font-weight:800;color:var(--azul-dark);line-height:1}
@keyframes floatUp{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}

.features{padding:96px 80px;background:#fff}
.section-header{text-align:center;margin-bottom:56px}
.section-header h2{font-family:'Plus Jakarta Sans',sans-serif;font-size:36px;font-weight:800;color:var(--azul-dark);margin-bottom:12px}
.section-header p{font-size:16px;color:var(--gris);max-width:520px;margin:0 auto}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.feat-card{padding:28px;border-radius:18px;border:1.5px solid #e2e8f0;transition:.22s}
.feat-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.08);border-color:var(--verde)}
.feat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:18px}
.feat-card h3{font-family:'Plus Jakarta Sans',sans-serif;font-size:17px;font-weight:700;margin-bottom:10px}
.feat-card p{font-size:14px;color:var(--gris);line-height:1.65}

.stats-banner{background:linear-gradient(135deg,#0f172a 0%,#134e31 60%,#16a34a 100%);padding:72px 80px;display:grid;grid-template-columns:repeat(4,1fr);gap:24px;position:relative;overflow:hidden}
.stats-banner::before{content:'';position:absolute;right:-60px;top:-80px;width:360px;height:360px;background:radial-gradient(circle,rgba(34,197,94,.2) 0%,transparent 70%);border-radius:50%}
.stat-item{text-align:center;position:relative;z-index:1}
.stat-item .num{font-family:'Plus Jakarta Sans',sans-serif;font-size:42px;font-weight:800;color:var(--verde);line-height:1;margin-bottom:6px}
.stat-item .lbl{font-size:14px;color:rgba(255,255,255,.7)}

.cta-section{padding:96px 80px;text-align:center;background:linear-gradient(135deg,#0f172a 0%,#1a3a4a 50%,#0f2d1a 100%);position:relative;overflow:hidden}
.cta-section::before{content:'';position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(34,197,94,.12) 0%,transparent 60%);border-radius:50%}
.cta-section h2{font-family:'Plus Jakarta Sans',sans-serif;font-size:40px;font-weight:800;color:#fff;margin-bottom:14px;position:relative;z-index:1}
.cta-section p{font-size:16px;color:rgba(255,255,255,.65);margin-bottom:32px;position:relative;z-index:1}
.cta-section .btn-primary{font-size:16px;padding:15px 36px;position:relative;z-index:1}

footer{background:#0f172a;padding:56px 80px 32px}
.footer-grid{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:40px;margin-bottom:40px}
.footer-brand p{font-size:13px;color:rgba(255,255,255,.45);margin-top:12px;line-height:1.6;max-width:220px}
.footer-col h4{font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:13px;color:#fff;margin-bottom:14px}
.footer-col a{display:block;font-size:13px;color:rgba(255,255,255,.45);text-decoration:none;margin-bottom:8px;transition:.15s}
.footer-col a:hover{color:var(--verde)}
.footer-bottom{border-top:1px solid rgba(255,255,255,.08);padding-top:24px;text-align:center;font-size:13px;color:rgba(255,255,255,.3)}

.fade-in{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
.fade-in.visible{opacity:1;transform:translateY(0)}

@media(max-width:1024px){
    .hero{grid-template-columns:1fr;padding:100px 32px 60px;text-align:center}
    .hero-visual{display:none}
    .hero-title{font-size:38px}
    .features-grid{grid-template-columns:repeat(2,1fr)}
    .stats-banner{grid-template-columns:repeat(2,1fr)}
    .footer-grid{grid-template-columns:1fr 1fr}
    .features,.cta-section,.stats-banner,footer{padding-left:32px;padding-right:32px}
}
</style>
</head>
<body>

<nav class="navbar">
    <a class="nav-logo" href="#">
        <div class="nav-logo-icon">🥗</div>
        <div class="nav-logo-text">
            <div class="name">NutriPredict Escolar</div>
            <div class="sub">Nutrición Inteligente</div>
        </div>
    </a>
    <a href="login.php" class="nav-btn">→ Iniciar Sesión</a>
</nav>

<section class="hero">
    <div class="hero-content fade-in">
        <div class="hero-badge">🎯 Tecnología Avanzada para la Salud Escolar</div>
        <h1 class="hero-title">Nutrición Escolar <span>Inteligente y Predictiva</span></h1>
        <p class="hero-desc">Monitorea, predice y previene deficiencias nutricionales en estudiantes de básica primaria mediante análisis de datos avanzados.</p>
        <div class="hero-btns">
            <a href="login.php" class="btn-primary">Comenzar Ahora ›</a>
            <a href="#features" class="btn-outline">Ver Características</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat"><div class="num">487</div><div class="lbl">Estudiantes</div></div>
            <div class="hero-stat"><div class="num">94.2%</div><div class="lbl">Precisión</div></div>
            <div class="hero-stat"><div class="num">89%</div><div class="lbl">Óptima Nutrición</div></div>
        </div>
    </div>
    <div class="hero-visual fade-in">
        <div class="hero-img-wrap">
            <img src="https://images.unsplash.com/photo-1529390079861-591de354faf5?w=700&q=80" alt="Niños en escuela">
            <div class="float-card tr">
                <div class="float-icon" style="background:#dcfce7">📈</div>
                <div><div class="float-lbl">Mejora</div><div class="float-val" style="color:var(--verde)">+23%</div></div>
            </div>
            <div class="float-card bl">
                <div class="float-icon" style="background:#fef2f2">🔔</div>
                <div><div class="float-lbl">Alertas Activas</div><div class="float-val">4</div></div>
            </div>
        </div>
    </div>
</section>

<section class="features" id="features">
    <div class="section-header fade-in">
        <h2>Todo lo que necesitas para cuidar la nutrición escolar</h2>
        <p>Herramientas completas para el bienestar nutricional de tus estudiantes</p>
    </div>
    <div class="features-grid">
        <div class="feat-card fade-in" style="background:#f0fdf4"><div class="feat-icon" style="background:#22c55e">📊</div><h3>Análisis Predictivo</h3><p>Algoritmos avanzados con 94.2% de precisión para detectar riesgos nutricionales antes de que se desarrollen.</p></div>
        <div class="feat-card fade-in" style="background:#eff6ff"><div class="feat-icon" style="background:#3b82f6">👥</div><h3>Gestión Completa</h3><p>Administra perfiles de estudiantes, historial nutricional y seguimiento personalizado de cada niño.</p></div>
        <div class="feat-card fade-in" style="background:#faf5ff"><div class="feat-icon" style="background:#a855f7">🍎</div><h3>Menús Inteligentes</h3><p>Planificación automática de menús balanceados basados en necesidades nutricionales específicas.</p></div>
        <div class="feat-card fade-in" style="background:#fff7ed"><div class="feat-icon" style="background:#f97316">❤️</div><h3>Alertas Tempranas</h3><p>Notificaciones automáticas cuando se detectan deficiencias o riesgos nutricionales en los estudiantes.</p></div>
        <div class="feat-card fade-in" style="background:#fdf4ff"><div class="feat-icon" style="background:#ec4899">📄</div><h3>Reportes Detallados</h3><p>Informes completos con gráficos, estadísticas y recomendaciones para padres y directivos.</p></div>
        <div class="feat-card fade-in" style="background:#f0fdfa"><div class="feat-icon" style="background:#14b8a6">🔒</div><h3>Datos Seguros</h3><p>Protección de información médica y nutricional con los más altos estándares de seguridad.</p></div>
    </div>
</section>

<section class="stats-banner">
    <div class="stat-item fade-in"><div class="num">487</div><div class="lbl">Estudiantes Monitoreados</div></div>
    <div class="stat-item fade-in"><div class="num">94.2%</div><div class="lbl">Precisión del Modelo</div></div>
    <div class="stat-item fade-in"><div class="num">+23%</div><div class="lbl">Mejora Nutricional</div></div>
    <div class="stat-item fade-in"><div class="num">89%</div><div class="lbl">Óptima Nutrición</div></div>
</section>

<section class="cta-section">
    <h2>Comienza a transformar la<br>nutrición escolar hoy</h2>
    <p>Únete a las instituciones que ya están mejorando la salud de sus estudiantes</p>
    <a href="login.php" class="btn-primary">Acceder al Sistema ›</a>
</section>

<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <a class="nav-logo" href="#"><div class="nav-logo-icon">🥗</div><div class="nav-logo-text"><div class="name" style="color:#fff">NutriPredict Escolar</div></div></a>
            <p>Sistema predictivo de deficiencias nutricionales para básica primaria.</p>
        </div>
        <div class="footer-col"><h4>Producto</h4><a href="#features">Características</a><a href="#">Precios</a><a href="#">Demo</a></div>
        <div class="footer-col"><h4>Recursos</h4><a href="#">Documentación</a><a href="#">Guías</a><a href="#">Soporte</a></div>
        <div class="footer-col"><h4>Empresa</h4><a href="#">Acerca de</a><a href="#">Contacto</a><a href="#">Privacidad</a></div>
    </div>
    <div class="footer-bottom">© 2026 NutriPredict Escolar. Todos los derechos reservados.</div>
</footer>

<script>
const obs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) setTimeout(() => e.target.classList.add('visible'), i * 80);
    });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => obs.observe(el));
</script>
</body>
</html>
