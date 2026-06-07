<?php
// login.php — Punto de entrada: conecta AuthPresenter con la vista de login
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
require_once 'includes/db.php';
require_once 'presenters/AuthPresenter.php';

$presenter = new AuthPresenter($conn);

if ($presenter->manejarLogin()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — NutriPredict</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="apple-touch-icon" href="css/icons/icon-192.png">
    <script>if('serviceWorker' in navigator){window.addEventListener('load',function(){navigator.serviceWorker.register('sw.js').catch(function(){});});}</script>
</head>
<body class="login-page">
<div class="login-box">
    <div style="margin-bottom:16px">
        <a href="index.php" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--gris);text-decoration:none;padding:6px 12px;border-radius:8px;border:1.5px solid #e2e8f0;transition:.18s;font-family:inherit" onmouseover="this.style.borderColor='var(--verde)';this.style.color='var(--verde-dark)'" onmouseout="this.style.borderColor='#e2e8f0';this.style.color='var(--gris)'">
            ← Volver a la página principal
        </a>
    </div>
    <div class="login-logo">
        <div class="icon">🥗</div>
        <h1>NutriPredict Escolar</h1>
        <p>Ingresa tus credenciales para continuar</p>
    </div>
    <?php if($presenter->error): ?>
    <div class="alert-box error">❌ <?=htmlspecialchars($presenter->error)?></div>
    <?php endif; ?>
    <form method="POST">
        <?php csrf_field(); ?>
        <div class="form-group"><label class="form-label">Correo Electrónico</label><input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required value="<?=htmlspecialchars($_POST['email']??'')?>"></div>
        <div class="form-group"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" placeholder="••••••••" required></div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:6px">→ Iniciar Sesión</button>
    </form>
    <div style="margin-top:16px;background:var(--gris-light);border-radius:9px;overflow:hidden;font-size:12px">
        <div style="padding:8px 12px;background:#e2e8f0;font-weight:700;color:var(--azul-dark);font-size:11.5px">🔑 Credenciales de prueba</div>
        <?php
        $creds = [
            ['','Administrador',    'admin@nutripredict.edu.co'],
            ['','Enc. Restaurante', 'restaurante@nutripredict.edu.co'],
            ['','Docente',           'docente@nutripredict.edu.co'],
            ['','Directora',         'directora@nutripredict.edu.co'],
        ];
        foreach ($creds as [$ico, $rol, $email]): ?>
        <div onclick="document.querySelector('[name=email]').value='<?=$email?>';document.querySelector('[name=password]').value='demo123'"
             style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-bottom:1px solid #e2e8f0;cursor:pointer;transition:.15s"
             onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='transparent'">
            <span style="font-size:15px"><?=$ico?></span>
            <div style="flex:1">
                <div style="font-weight:600;color:var(--azul-dark)"><?=$rol?></div>
                <div style="color:var(--gris)"><?=$email?></div>
            </div>
            <span style="font-size:10px;color:var(--gris);background:#fff;padding:2px 7px;border-radius:20px;border:1px solid #e2e8f0">demo123</span>
        </div>
        <?php endforeach; ?>
        <div style="padding:6px 12px;font-size:11px;color:var(--gris)"></div>
    </div>
</div>
</body>
</html>
