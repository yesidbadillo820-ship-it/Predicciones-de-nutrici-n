<?php // views/estudiantes_view.php — VISTA: solo HTML ?>

<?php if($presenter->msg): ?><div class="alert-box success">✅ <?=htmlspecialchars($presenter->msg)?></div><?php endif; ?>
<?php if($presenter->err): ?><div class="alert-box error">❌ <?=htmlspecialchars($presenter->err)?></div><?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
    <form method="GET" style="display:flex;gap:10px">
        <input type="text" name="q" class="form-control" placeholder="Buscar estudiante…" value="<?=htmlspecialchars($buscar)?>" style="width:240px">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <?php if($buscar): ?><a href="estudiantes.php" class="btn btn-secondary">✕</a><?php endif; ?>
    </form>
    <?php if(tienePermiso('estudiantes')): ?>
    <button class="btn btn-primary" data-modal="modalCrear">➕ Nuevo Estudiante</button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nombre</th><th>Grado</th><th>Género</th><th>Peso</th><th>Talla</th><th>Riesgo</th><?php if(tienePermiso('estudiantes')): ?><th>Acciones</th><?php endif; ?></tr></thead>
        <tbody>
        <?php if($estudiantes&&$estudiantes->num_rows>0): $i=1; while($e=$estudiantes->fetch_assoc()): ?>
        <tr>
            <td><?=$i++?></td>
            <td><strong><?=htmlspecialchars($e['nombre'].' '.$e['apellido'])?></strong></td>
            <td><?=htmlspecialchars($e['grado'])?></td>
            <td><?=$e['genero']==='M'?'👦 M':'👧 F'?></td>
            <td><?=$e['peso_kg']?$e['peso_kg'].' kg':'—'?></td>
            <td><?=$e['talla_cm']?$e['talla_cm'].' cm':'—'?></td>
            <td><span class="chip chip-<?=$e['nivel_riesgo']?>"><?=ucfirst(str_replace('_',' ',$e['nivel_riesgo']))?></span></td>
            <?php if(tienePermiso('estudiantes')): ?>
            <td style="display:flex;gap:6px">
                <button class="btn btn-secondary btn-sm" onclick='editarEst(<?=json_encode($e)?>)'>✏️ Editar</button>
                <a href="estudiantes.php?del=<?=$e['id']?>" class="btn btn-danger btn-sm" data-confirm="¿Eliminar a <?=htmlspecialchars($e['nombre'])?>?">🗑️</a>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8"><div class="empty-state"><div class="icon">👥</div><p>No hay estudiantes registrados</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>

<?php if(tienePermiso('estudiantes')): ?>
<!-- MODAL CREAR -->
<div class="modal-overlay" id="modalCrear">
<div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalCrear').classList.remove('show')">✕</button>
    <div class="modal-title">➕ Nuevo Estudiante</div>
    <form method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="accion" value="crear">
        <div class="form-row">
            <div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Apellido *</label><input type="text" name="apellido" class="form-control" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Fecha Nacimiento *</label><input type="date" name="fecha_nac" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Género *</label>
                <select name="genero" class="form-control"><option value="M">Masculino</option><option value="F">Femenino</option></select>
            </div>
        </div>
        <div class="form-group"><label class="form-label">Grado *</label>
            <select name="id_grado" class="form-control" required>
                <option value="">-- Seleccionar --</option>
                <?php $grados->data_seek(0); while($g=$grados->fetch_assoc()): ?>
                <option value="<?=$g['id']?>"><?=htmlspecialchars($g['nombre'])?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Peso (kg)</label><input type="number" name="peso_kg" class="form-control" step="0.01"></div>
            <div class="form-group"><label class="form-label">Talla (cm)</label><input type="number" name="talla_cm" class="form-control" step="0.01"></div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalCrear').classList.remove('show')">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div></div>

<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modalEditar">
<div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalEditar').classList.remove('show')">✕</button>
    <div class="modal-title">✏️ Editar Estudiante</div>
    <form method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id" id="edit_id">
        <div class="form-row">
            <div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="edit_nombre" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Apellido *</label><input type="text" name="apellido" id="edit_apellido" class="form-control" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Fecha Nac.</label><input type="date" name="fecha_nac" id="edit_fnac" class="form-control"></div>
            <div class="form-group"><label class="form-label">Género</label>
                <select name="genero" id="edit_genero" class="form-control"><option value="M">Masculino</option><option value="F">Femenino</option></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Grado</label>
                <select name="id_grado" id="edit_grado" class="form-control">
                    <?php $grados->data_seek(0); while($g=$grados->fetch_assoc()): ?>
                    <option value="<?=$g['id']?>"><?=htmlspecialchars($g['nombre'])?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Nivel Riesgo</label>
                <select name="nivel_riesgo" id="edit_riesgo" class="form-control">
                    <option value="sin_riesgo">Sin Riesgo</option><option value="bajo">Bajo</option><option value="medio">Medio</option><option value="alto">Alto</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Peso (kg)</label><input type="number" name="peso_kg" id="edit_peso" class="form-control" step="0.01"></div>
            <div class="form-group"><label class="form-label">Talla (cm)</label><input type="number" name="talla_cm" id="edit_talla" class="form-control" step="0.01"></div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalEditar').classList.remove('show')">Cancelar</button>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div></div>

<script>
function editarEst(e){
    document.getElementById('edit_id').value=e.id;
    document.getElementById('edit_nombre').value=e.nombre;
    document.getElementById('edit_apellido').value=e.apellido;
    document.getElementById('edit_fnac').value=e.fecha_nac;
    document.getElementById('edit_genero').value=e.genero;
    document.getElementById('edit_grado').value=e.id_grado;
    document.getElementById('edit_peso').value=e.peso_kg;
    document.getElementById('edit_talla').value=e.talla_cm;
    document.getElementById('edit_riesgo').value=e.nivel_riesgo;
    document.getElementById('modalEditar').classList.add('show');
}
</script>
<?php endif; ?>
