<?php
// includes/header.php
$usuario = getUsuario();
require_once 'includes/roles.php';
require_once 'includes/db.php';
$n = $conn->query("SELECT COUNT(*) AS t FROM alertas WHERE estado='activa'")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'NutriPredict Escolar') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <!-- PWA -->
    <meta name="theme-color" content="#16a34a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NutriPredict">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="apple-touch-icon" href="css/icons/icon-192.png">
    <script>if('serviceWorker' in navigator){window.addEventListener('load',function(){navigator.serviceWorker.register('sw.js').catch(function(){});});}</script>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🥗</div>
        <div class="logo-text">
            <div class="name">NutriPredict</div>
            <div class="sub">Escolar · Nutrición Inteligente</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <span class="nav-section-label">Principal</span>
        <a class="nav-item <?= ($active_menu=='dashboard')?'active':'' ?>" href="dashboard.php"><div class="icon">🏠</div> Panel Principal</a>

        <?php if(tienePermiso('estudiantes')||tienePermiso('estudiantes_ver')): ?>
        <span class="nav-section-label">Gestión</span>
        <a class="nav-item <?= ($active_menu=='estudiantes')?'active':'' ?>" href="estudiantes.php"><div class="icon">👥</div> Estudiantes</a>
        <?php endif; ?>

        <?php if(tienePermiso('menus')||tienePermiso('menus_ver')): ?>
        <?php if(!tienePermiso('estudiantes')&&!tienePermiso('estudiantes_ver')): ?><span class="nav-section-label">Gestión</span><?php endif; ?>
        <a class="nav-item <?= ($active_menu=='menus')?'active':'' ?>" href="menus.php"><div class="icon">📋</div> Menús del Día</a>
        <?php endif; ?>

        <?php if(tienePermiso('alimentos')): ?>
        <a class="nav-item <?= ($active_menu=='alimentos')?'active':'' ?>" href="alimentos.php"><div class="icon">🥦</div> Catálogo Alimentos</a>
        <?php endif; ?>

        <?php if(tienePermiso('asistencia')): ?>
        <a class="nav-item <?= ($active_menu=='asistencia')?'active':'' ?>" href="asistencia.php"><div class="icon">✅</div> Asistencia</a>
        <?php endif; ?>

        <span class="nav-section-label">Análisis</span>

        <?php if(tienePermiso('alertas')||tienePermiso('alertas_ver')): ?>
        <a class="nav-item <?= ($active_menu=='alertas')?'active':'' ?>" href="alertas.php">
            <div class="icon">🔔</div> Alertas
            <?php if($n>0) echo "<span class='nav-badge'>$n</span>"; ?>
        </a>
        <?php endif; ?>

        <?php if(tienePermiso('predictivo')||tienePermiso('predictivo_ver')): ?>
        <a class="nav-item <?= ($active_menu=='predictivo')?'active':'' ?>" href="predictivo.php"><div class="icon">🧠</div> Análisis Predictivo</a>
        <?php endif; ?>

        <?php if(tienePermiso('reportes')||tienePermiso('reportes_ver')): ?>
        <a class="nav-item <?= ($active_menu=='reportes')?'active':'' ?>" href="reportes.php"><div class="icon">📄</div> Reportes</a>
        <?php endif; ?>

        <?php if(tienePermiso('usuarios')): ?>
        <span class="nav-section-label">Administración</span>
        <a class="nav-item <?= ($active_menu=='usuarios')?'active':'' ?>" href="usuarios.php"><div class="icon">👤</div> Usuarios y Roles</a>
        <?php endif; ?>

        <span class="nav-section-label">Soporte</span>
        <a class="nav-item <?= ($active_menu=='ayuda')?'active':'' ?>" href="ayuda.php"><div class="icon">📚</div> Centro de Ayuda</a>
    </nav>
    <div class="sidebar-footer">
        <?php
        $ri = ['admin'=>['🛡️','Administrador','#22c55e'],'encargado_restaurante'=>['👨‍🍳','Encargado','#f97316'],'docente'=>['📚','Docente','#8b5cf6'],'directora'=>['🏫','Directora','#0ea5e9']][$usuario['rol']] ?? ['👤',ucfirst($usuario['rol']),'#94a3b8'];
        ?>
        <div style="padding:0 12px 8px">
            <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);padding:4px 12px;border-radius:20px;font-size:11px">
                <?=$ri[0]?> <span style="color:<?=$ri[2]?>;font-weight:700"><?=$ri[1]?></span>
            </span>
        </div>
        <div class="user-card">
            <div class="avatar"><?=strtoupper(substr($usuario['nombre'],0,2))?></div>
            <div class="user-info">
                <div class="uname"><?=htmlspecialchars($usuario['nombre'])?></div>
                <div class="urole"><?=htmlspecialchars($usuario['email'])?></div>
            </div>
            <a href="logout.php" style="margin-left:auto;color:rgba(255,255,255,.4);font-size:18px;text-decoration:none" title="Cerrar sesión">⏏</a>
        </div>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="main">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:12px">
            <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Abrir menú">☰</button>
            <div>
                <div class="topbar-title"><?=htmlspecialchars($page_title??'')?></div>
                <div class="topbar-sub"><?=htmlspecialchars($page_sub??'')?></div>
            </div>
        </div>
        <div class="topbar-actions">
            <div class="search-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input class="search-bar" type="text" id="globalSearch" placeholder="Buscar…">
            </div>
            <?php if(tienePermiso('alertas')||tienePermiso('alertas_ver')): ?>
            <div class="icon-btn" onclick="window.location='alertas.php'" title="Alertas">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <?php if($n>0): ?><span class="notif-dot"></span><?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="date-pill">📅 <?=date('d M Y')?></div>
            <button id="btn-dark-mode" onclick="toggleDarkMode()" title="Modo oscuro">🌙</button>
            <a href="ayuda.php" class="icon-btn" title="Centro de Ayuda" style="text-decoration:none;font-size:17px">❓</a>
        </div>
    </header>
    <main class="content">
