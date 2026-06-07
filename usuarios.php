<?php
// usuarios.php
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
requirePermiso('usuarios');
require_once 'presenters/AuthPresenter.php';

$presenter = new UsuarioPresenter($conn);
$presenter->manejarRequest();
$usuarios = $presenter->obtenerUsuarios();

$page_title='Usuarios y Roles'; $page_sub='Control de acceso'; $active_menu='usuarios';
include 'includes/header.php';
?>
<?php if($presenter->msg): ?><div class="alert-box success">✅ <?=htmlspecialchars($presenter->msg)?></div><?php endif; ?>
<?php if($presenter->err): ?><div class="alert-box error">❌ <?=htmlspecialchars($presenter->err)?></div><?php endif; ?>

<div class="grid-3" style="margin-bottom:20px">
<?php foreach(['admin'=>['🛡️','Administrador','#22c55e','Acceso total al sistema.'],'encargado_restaurante'=>['👨‍🍳','Enc. Restaurante','#f97316','Registra menús y asistencia.'],'docente'=>['📚','Docente','#8b5cf6','Consulta y registra asistencia.'],'directora'=>['🏫','Directora','#0ea5e9','Ve reportes y predicciones.']] as $rol=>[$ico,$lbl,$col,$desc]):
    $cnt=$presenter->contarPorRol($rol); ?>
<div class="card" style="border-top:3px solid <?=$col?>"><div class="card-body">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
        <span style="font-size:22px"><?=$ico?></span>
        <div><div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:14px"><?=$lbl?></div><span style="font-size:11px;font-weight:700;color:<?=$col?>"><?=$cnt?> activo(s)</span></div>
    </div>
    <p style="font-size:12px;color:var(--gris)"><?=$desc?></p>
</div></div>
<?php endforeach; ?>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-header"><div class="card-title">Usuarios Registrados</div><button class="btn btn-primary" data-modal="modalCrear">➕ Nuevo Usuario</button></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php $chips=['admin'=>['chip-verde','🛡️ Admin'],'encargado_restaurante'=>['chip-amarillo','👨‍🍳 Restaurante'],'docente'=>['chip','📚 Docente'],'directora'=>['chip','🏫 Directora']];
        while($u=$usuarios->fetch_assoc()): $rc=$chips[$u['rol']]??['chip','👤']; ?>
        <tr style="<?=!$u['activo']?'opacity:.5':''?>">
            <td><strong><?=htmlspecialchars($u['nombre'])?></strong></td>
            <td style="font-size:12.5px;color:var(--gris)"><?=htmlspecialchars($u['email'])?></td>
            <td><span class="chip <?=$rc[0]?>"><?=$rc[1]?></span></td>
            <td><span class="chip <?=$u['activo']?'chip-verde':'chip-rojo'?>"><?=$u['activo']?'✅ Activo':'❌ Inactivo'?></span></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap">
                <button class="btn btn-secondary btn-sm" onclick='abrirRol(<?=$u['id']?>,"<?=$u['rol']?>","<?=htmlspecialchars($u['nombre'])?>")'>🔄 Rol</button>
                <button class="btn btn-secondary btn-sm" onclick='abrirPass(<?=$u['id']?>,"<?=htmlspecialchars($u['nombre'])?>")'>🔑 Clave</button>
                <?php if($u['id']!=$_SESSION['usuario_id']): ?>
                <a href="usuarios.php?toggle=<?=$u['id']?>" class="btn btn-secondary btn-sm" data-confirm="¿<?=$u['activo']?'Desactivar':'Activar'?> usuario?"><?=$u['activo']?'🚫':'✅'?></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table></div>
</div>

<!-- MODALES -->
<div class="modal-overlay" id="modalCrear"><div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalCrear').classList.remove('show')">✕</button>
    <div class="modal-title">➕ Nuevo Usuario</div>
    <form method="POST"><input type="hidden" name="accion" value="crear">
        <div class="form-row">
            <div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Correo *</label><input type="email" name="email" class="form-control" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Contraseña *</label><input type="password" name="password" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Rol</label>
                <select name="rol" class="form-control"><option value="admin">🛡️ Admin</option><option value="encargado_restaurante">👨‍🍳 Restaurante</option><option value="docente" selected>📚 Docente</option><option value="directora">🏫 Directora</option></select>
            </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end"><button type="button" class="btn btn-secondary" onclick="document.getElementById('modalCrear').classList.remove('show')">Cancelar</button><button type="submit" class="btn btn-primary">Crear</button></div>
    </form>
</div></div>

<div class="modal-overlay" id="modalRol"><div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalRol').classList.remove('show')">✕</button>
    <div class="modal-title">🔄 Cambiar Rol</div>
    <form method="POST"><input type="hidden" name="accion" value="rol"><input type="hidden" name="id" id="rol_id">
        <div class="form-group"><label class="form-label">Usuario</label><div id="rol_nombre" style="padding:8px 12px;background:var(--gris-light);border-radius:9px;font-size:13px;font-weight:600"></div></div>
        <div class="form-group"><label class="form-label">Nuevo Rol</label>
            <select name="rol" id="rol_select" class="form-control"><option value="admin">🛡️ Admin</option><option value="encargado_restaurante">👨‍🍳 Restaurante</option><option value="docente">📚 Docente</option><option value="directora">🏫 Directora</option></select>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end"><button type="button" class="btn btn-secondary" onclick="document.getElementById('modalRol').classList.remove('show')">Cancelar</button><button type="submit" class="btn btn-primary">Actualizar</button></div>
    </form>
</div></div>

<div class="modal-overlay" id="modalPass"><div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalPass').classList.remove('show')">✕</button>
    <div class="modal-title">🔑 Cambiar Contraseña</div>
    <form method="POST"><input type="hidden" name="accion" value="resetpass"><input type="hidden" name="id" id="pass_id">
        <div class="form-group"><label class="form-label">Usuario</label><div id="pass_nombre" style="padding:8px 12px;background:var(--gris-light);border-radius:9px;font-size:13px;font-weight:600"></div></div>
        <div class="form-group"><label class="form-label">Nueva Contraseña *</label><input type="password" name="nueva_pass" id="nueva_pass" class="form-control" required></div>
        <div style="display:flex;gap:10px;justify-content:flex-end"><button type="button" class="btn btn-secondary" onclick="document.getElementById('modalPass').classList.remove('show')">Cancelar</button><button type="submit" class="btn btn-primary">Cambiar</button></div>
    </form>
</div></div>

<script>
function abrirRol(id,rol,nombre){document.getElementById('rol_id').value=id;document.getElementById('rol_nombre').textContent=nombre;document.getElementById('rol_select').value=rol;document.getElementById('modalRol').classList.add('show');}
function abrirPass(id,nombre){document.getElementById('pass_id').value=id;document.getElementById('pass_nombre').textContent=nombre;document.getElementById('nueva_pass').value='';document.getElementById('modalPass').classList.add('show');}
</script>
<?php include 'includes/footer.php'; ?>
