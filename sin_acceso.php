<?php
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/roles.php';
$page_title='Sin Acceso'; $page_sub=''; $active_menu='';
include 'includes/header.php';
?>
<div style="display:flex;align-items:center;justify-content:center;min-height:400px">
    <div style="text-align:center;max-width:400px">
        <div style="font-size:60px;margin-bottom:16px">🔒</div>
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:22px;margin-bottom:8px">Sin Acceso</div>
        <p style="color:var(--gris);font-size:14px;margin-bottom:20px">Tu rol <strong><?=ucfirst(str_replace('_',' ',$_SESSION['usuario']['rol']))?></strong> no tiene permisos para esta sección.</p>
        <a href="dashboard.php" class="btn btn-primary">← Volver al Panel</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
