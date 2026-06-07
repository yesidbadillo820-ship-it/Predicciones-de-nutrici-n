<?php
// alertas.php — Punto de entrada MVP
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
require_once 'presenters/AlertaPresenter.php';

$presenter = new AlertaPresenter($conn);
$presenter->manejarRequest();

$filtro    = $_GET['estado'] ?? 'activa';
$alertas   = $presenter->obtenerAlertas($filtro);
$conteos   = $presenter->obtenerConteos();
$estudiantes = $presenter->obtenerEstudiantes();

function time_ago($dt){$d=time()-strtotime($dt);if($d<3600)return 'Hace '.floor($d/60).' min';if($d<86400)return 'Hace '.floor($d/3600).' h';return 'Hace '.floor($d/86400).' día(s)';}

$page_title='Alertas Nutricionales'; $page_sub='Deficiencias detectadas'; $active_menu='alertas';
include 'includes/header.php';
?>
<?php if($presenter->msg): ?><div class="alert-box success">✅ <?=htmlspecialchars($presenter->msg)?></div><?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
    <div style="display:flex;gap:8px">
        <a href="alertas.php?estado=activa"   class="btn <?=$filtro==='activa'  ?'btn-primary':'btn-secondary'?> btn-sm">🔴 Activas (<?=$conteos['activa']?>)</a>
        <a href="alertas.php?estado=resuelta" class="btn <?=$filtro==='resuelta'?'btn-primary':'btn-secondary'?> btn-sm">✅ Resueltas (<?=$conteos['resuelta']?>)</a>
        <a href="alertas.php?estado=ignorada" class="btn <?=$filtro==='ignorada'?'btn-primary':'btn-secondary'?> btn-sm">🔕 Ignoradas (<?=$conteos['ignorada']?>)</a>
    </div>
    <?php if(tienePermiso('alertas')): ?><button class="btn btn-primary" data-modal="modalAlerta">➕ Nueva Alerta</button><?php endif; ?>
</div>

<div class="card">
    <div class="table-wrap"><table>
        <thead><tr><th>Estudiante</th><th>Grado</th><th>Deficiencia</th><th>Descripción</th><th>Nivel</th><th>Fecha</th><?php if($filtro==='activa'&&tienePermiso('alertas')): ?><th>Acciones</th><?php endif; ?></tr></thead>
        <tbody>
        <?php if($alertas&&$alertas->num_rows>0): while($a=$alertas->fetch_assoc()):
            $chip=['alta'=>'chip-rojo','media'=>'chip-amarillo','baja'=>'chip-verde','critica'=>'chip-rojo'][$a['nivel']]??'chip-amarillo'; ?>
        <tr>
            <td><strong><?=htmlspecialchars($a['nombre'].' '.$a['apellido'])?></strong></td>
            <td><?=htmlspecialchars($a['grado'])?></td>
            <td><?=AlertaPresenter::icono($a['tipo_deficiencia'])?> <?=htmlspecialchars($a['tipo_deficiencia'])?></td>
            <td style="max-width:240px;font-size:12px"><?=htmlspecialchars($a['descripcion'])?></td>
            <td><span class="chip <?=$chip?>"><?=ucfirst($a['nivel'])?></span></td>
            <td style="font-size:12px;color:var(--gris)"><?=time_ago($a['fecha_creacion'])?></td>
            <?php if($filtro==='activa'&&tienePermiso('alertas')): ?>
            <td style="display:flex;gap:6px">
                <a href="alertas.php?resolver=<?=$a['id']?>&estado=activa" class="btn btn-primary btn-sm">✅</a>
                <a href="alertas.php?ignorar=<?=$a['id']?>&estado=activa"  class="btn btn-secondary btn-sm">🔕</a>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="7"><div class="empty-state"><div class="icon">✅</div><p>No hay alertas <?=htmlspecialchars($filtro)?>s</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>

<?php if(tienePermiso('alertas')): ?>
<div class="modal-overlay" id="modalAlerta">
<div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalAlerta').classList.remove('show')">✕</button>
    <div class="modal-title">⚠️ Nueva Alerta Manual</div>
    <form method="POST">
        <input type="hidden" name="accion" value="crear">
        <div class="form-group"><label class="form-label">Estudiante *</label>
            <select name="id_estudiante" class="form-control" required>
                <option value="">-- Seleccionar --</option>
                <?php while($e=$estudiantes->fetch_assoc()): ?>
                <option value="<?=$e['id']?>"><?=htmlspecialchars($e['nombre'].' '.$e['apellido'])?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Tipo *</label>
                <select name="tipo_deficiencia" class="form-control">
                    <option value="Hierro">🩸 Hierro</option><option value="Calcio">🦴 Calcio</option>
                    <option value="Proteinas">💪 Proteínas</option><option value="Vitamina D">☀️ Vitamina D</option>
                    <option value="Zinc">🔬 Zinc</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Nivel</label>
                <select name="nivel" class="form-control"><option value="baja">Baja</option><option value="media" selected>Media</option><option value="alta">Alta</option></select>
            </div>
        </div>
        <div class="form-group"><label class="form-label">Descripción *</label><textarea name="descripcion" class="form-control" rows="3" required></textarea></div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalAlerta').classList.remove('show')">Cancelar</button>
            <button type="submit" class="btn btn-primary">Crear Alerta</button>
        </div>
    </form>
</div></div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
