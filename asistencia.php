<?php
// asistencia.php — Punto de entrada MVP
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
require_once 'presenters/AsistenciaPresenter.php';

$presenter = new AsistenciaPresenter($conn);
$presenter->manejarRequest();

$fecha_sel   = $_GET['fecha'] ?? date('Y-m-d');
$estudiantes = $presenter->obtenerEstudiantesConAsistencia($fecha_sel);
$resumen     = $presenter->obtenerResumen($fecha_sel);

$page_title='Asistencia'; $page_sub='Control diario al restaurante'; $active_menu='asistencia';
include 'includes/header.php';
?>
<?php if($presenter->msg): ?><div class="alert-box success">✅ <?=htmlspecialchars($presenter->msg)?></div><?php endif; ?>

<div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;flex-wrap:wrap">
    <form method="GET" style="display:flex;gap:10px;align-items:center">
        <label class="form-label" style="margin:0">📅 Fecha:</label>
        <input type="date" name="fecha" class="form-control" value="<?=htmlspecialchars($fecha_sel)?>" style="width:180px" onchange="this.form.submit()">
    </form>
    <?php if($resumen['total']>0): ?>
    <span class="chip chip-verde">✅ <?=$resumen['presentes']?> presentes</span>
    <span class="chip chip-rojo">❌ <?=$resumen['ausentes']?> ausentes</span>
    <?php endif; ?>
    <button class="btn btn-secondary btn-sm" onclick="marcarTodos(true)">✅ Marcar Todos</button>
    <button class="btn btn-secondary btn-sm" onclick="marcarTodos(false)">❌ Desmarcar</button>
</div>

<form method="POST">
<input type="hidden" name="fecha" value="<?=htmlspecialchars($fecha_sel)?>">
<div class="card" style="margin-bottom:14px">
    <div class="table-wrap"><table>
        <thead><tr><th>Estudiante</th><th>Grado</th><th>Asistió</th><th>Observación</th></tr></thead>
        <tbody>
        <?php if($estudiantes&&$estudiantes->num_rows>0): while($e=$estudiantes->fetch_assoc()): ?>
        <tr>
            <td><strong><?=htmlspecialchars($e['nombre'].' '.$e['apellido'])?></strong></td>
            <td><?=htmlspecialchars($e['grado'])?></td>
            <td><label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="asistencia[<?=$e['id']?>]" class="asist-check" value="1" <?=($e['asistio']??1)?'checked':''?> style="width:18px;height:18px;accent-color:var(--verde)">
                <span class="check-label" style="font-size:12px;font-weight:600"></span>
            </label></td>
            <td><input type="text" name="obs[<?=$e['id']?>]" class="form-control" style="padding:5px 10px;font-size:12px" placeholder="Observación…" value="<?=htmlspecialchars($e['observacion']??'')?>"></td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table></div>
</div>
<div style="text-align:right"><button type="submit" class="btn btn-primary">💾 Guardar Asistencia</button></div>
</form>
<script>
function marcarTodos(v){document.querySelectorAll('.asist-check').forEach(c=>{c.checked=v;act(c);})}
function act(c){const l=c.closest('label').querySelector('.check-label');if(l)l.textContent=c.checked?'✅ Presente':'❌ Ausente';}
document.querySelectorAll('.asist-check').forEach(c=>{act(c);c.addEventListener('change',()=>act(c));});
</script>
<?php include 'includes/footer.php'; ?>
