<?php
// menus.php — Punto de entrada MVP
// RF03: Registro diario con selección de alimentos del catálogo
// RF06: Agente inteligente genera alertas si nutriente < 70% ICBF
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
require_once 'presenters/MenuPresenter.php';

$presenter = new MenuPresenter($conn);
$presenter->manejarRequest(getUsuario()['id']);

$fecha_sel = $_GET['fecha'] ?? date('Y-m-d');
$menus     = $presenter->obtenerMenuFecha($fecha_sel);
$historial = $presenter->obtenerHistorial();
$alimentos = $presenter->obtenerAlimentos();
$icbf      = $presenter->obtenerNivelesICBF();

$page_title  = 'Menús del Restaurante';
$page_sub    = 'RF03 · Registro diario con cálculo automático de nutrientes ICBF';
$active_menu = 'menus';
include 'includes/header.php';
?>

<?php if($presenter->msg): ?>
<div class="alert-box <?=count($presenter->alertas_generadas)?'error':'success'?>">
    <?=count($presenter->alertas_generadas)?'⚠️':'✅'?> <?=htmlspecialchars($presenter->msg)?>
</div>
<?php endif; ?>

<?php if(count($presenter->alertas_generadas) > 0): ?>
<div style="background:var(--alerta-bg);border:1px solid #fed7aa;border-radius:12px;padding:16px;margin-bottom:16px">
    <div style="font-weight:700;font-size:14px;color:#92400e;margin-bottom:10px">
        🤖 Agente Inteligente — Deficiencias detectadas (RF06)
    </div>
    <?php foreach($presenter->alertas_generadas as $al): ?>
    <div style="background:#fff;border-radius:9px;padding:12px;margin-bottom:8px;border-left:3px solid var(--alerta)">
        <div style="font-size:13px;font-weight:600;color:var(--alerta);margin-bottom:4px">
            ⚠️ <?=htmlspecialchars($al['nutriente'])?> — solo <?=$al['pct']?>% del recomendado ICBF
        </div>
        <?php if($al['sugerencias']): ?>
        <div style="font-size:12px;color:var(--gris)">
            💡 Sugerencia: agregar <strong><?=htmlspecialchars(implode(', ', array_slice($al['sugerencias'],0,2)))?></strong> al menú del día siguiente.
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
    <form method="GET" style="display:flex;gap:10px;align-items:center">
        <label class="form-label" style="margin:0">📅 Fecha:</label>
        <input type="date" name="fecha" class="form-control" value="<?=htmlspecialchars($fecha_sel)?>" style="width:180px" onchange="this.form.submit()">
    </form>
    <?php if(tienePermiso('menus')): ?>
    <button class="btn btn-primary" data-modal="modalMenu">➕ Registrar Menú</button>
    <?php endif; ?>
</div>

<!-- TARJETAS DE TIEMPOS -->
<div class="grid-3" style="margin-bottom:18px">
<?php foreach(['desayuno'=>['🌅','Desayuno'],'almuerzo'=>['☀️','Almuerzo'],'merienda'=>['🌙','Merienda']] as $k=>[$ico,$lbl]):
    $m = $menus[$k] ?? null;
    $cobertura = $m ? json_decode($m['nutrientes_cubre'], true) ?? [] : [];
?>
<div class="card">
    <div class="card-header">
        <div><div class="card-title"><?=$ico?> <?=$lbl?></div><div class="card-sub"><?=htmlspecialchars($fecha_sel)?></div></div>
        <?php if($m && tienePermiso('menus')): ?>
        <a href="menus.php?del=<?=$m['id']?>&fecha=<?=$fecha_sel?>" class="btn btn-danger btn-sm" data-confirm="¿Eliminar menú?">🗑️</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
    <?php if($m): ?>
        <p style="font-size:13px;margin-bottom:12px"><?=htmlspecialchars($m['descripcion'])?></p>
        <div style="font-size:11px;font-weight:700;color:var(--gris);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Cobertura nutricional vs ICBF</div>
        <?php foreach($cobertura as $n):
            $col = $n['ok'] ? 'var(--verde)' : ($n['pct'] >= 50 ? 'var(--amarillo)' : 'var(--rojo)');
        ?>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px">
            <span style="font-size:11px;min-width:80px;color:var(--gris)"><?=htmlspecialchars($n['nombre'])?></span>
            <div style="flex:1;height:5px;background:#e2e8f0;border-radius:10px;overflow:hidden">
                <div style="height:100%;width:<?=min(100,$n['pct'])?>%;background:<?=$col?>;border-radius:10px"></div>
            </div>
            <span style="font-size:11px;font-weight:700;color:<?=$col?>;min-width:36px;text-align:right"><?=$n['pct']?>%</span>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state" style="padding:20px 0"><div class="icon">🍽️</div><p>Sin menú registrado</p>
        <?php if(tienePermiso('menus')): ?>
        <button class="btn btn-primary btn-sm" style="margin-top:8px" data-modal="modalMenu" onclick="document.getElementById('m_tipo').value='<?=$k?>'">Registrar</button>
        <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- HISTORIAL -->
<div class="card" style="margin-bottom:18px">
    <div class="card-header"><div class="card-title">Historial de Menús</div><div class="card-sub">Últimos 30 registros</div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Fecha</th><th>Tiempo</th><th>Descripción</th><th>Cobertura ICBF</th><th>Registrado por</th></tr></thead>
        <tbody>
        <?php $ti=['desayuno'=>'🌅','almuerzo'=>'☀️','merienda'=>'🌙'];
        if($historial && $historial->num_rows > 0): while($h=$historial->fetch_assoc()):
            $nuts = json_decode($h['nutrientes_cubre'], true) ?? [];
            $deficientes = array_filter($nuts, fn($n) => !$n['ok']);
        ?>
        <tr>
            <td><?=date('d/m/Y',strtotime($h['fecha']))?></td>
            <td><?=($ti[$h['tipo_tiempo']]??'🍽️').' '.ucfirst($h['tipo_tiempo'])?></td>
            <td style="font-size:12.5px"><?=htmlspecialchars(substr($h['descripcion'],0,55)).(strlen($h['descripcion'])>55?'…':'')?></td>
            <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap">
                <?php foreach($nuts as $n): ?>
                <span class="chip <?=$n['ok']?'chip-verde':'chip-rojo'?>" style="font-size:10px" title="<?=$n['pct']?>% ICBF">
                    <?=htmlspecialchars($n['nombre'])?>
                </span>
                <?php endforeach; ?>
                </div>
                <?php if(count($deficientes)>0): ?>
                <div style="font-size:10px;color:var(--rojo);margin-top:3px">⚠️ <?=count($deficientes)?> deficiencia(s)</div>
                <?php endif; ?>
            </td>
            <td style="font-size:12px;color:var(--gris)"><?=htmlspecialchars($h['registrado_por'])?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5" style="text-align:center;color:var(--gris);padding:20px">Sin historial</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>

<?php if(tienePermiso('menus')): ?>
<!-- MODAL REGISTRAR MENÚ -->
<div class="modal-overlay" id="modalMenu">
<div class="modal" style="max-width:620px">
    <button class="modal-close" onclick="document.getElementById('modalMenu').classList.remove('show')">✕</button>
    <div class="modal-title">🍽️ Registrar Menú — RF03</div>
    <p style="font-size:12px;color:var(--gris);margin-bottom:16px">El sistema calculará automáticamente los nutrientes y los comparará con los valores recomendados por el ICBF.</p>
    <form method="POST">
        <div class="form-row">
            <div class="form-group"><label class="form-label">Fecha *</label><input type="date" name="fecha" id="m_fecha" class="form-control" value="<?=htmlspecialchars($fecha_sel)?>" required></div>
            <div class="form-group"><label class="form-label">Tiempo *</label>
                <select name="tipo_tiempo" id="m_tipo" class="form-control">
                    <option value="desayuno">🌅 Desayuno</option>
                    <option value="almuerzo">☀️ Almuerzo</option>
                    <option value="merienda">🌙 Merienda</option>
                </select>
            </div>
        </div>
        <div class="form-group"><label class="form-label">Descripción del menú *</label>
            <textarea name="descripcion" class="form-control" rows="2" required placeholder="Ej: Arroz, frijoles, pollo asado, ensalada, jugo"></textarea>
        </div>

        <!-- SELECTOR DE ALIMENTOS DEL CATÁLOGO -->
        <div class="form-group">
            <label class="form-label">Alimentos del catálogo (RF03) <span style="color:var(--gris);font-weight:400">— Selecciona y define la porción en gramos</span></label>
            <div id="alimentos-lista" style="max-height:220px;overflow-y:auto;border:1.5px solid #e2e8f0;border-radius:9px;padding:10px">
                <?php $alimentos->data_seek(0); while($al=$alimentos->fetch_assoc()): ?>
                <div style="display:flex;align-items:center;gap:10px;padding:6px 4px;border-bottom:1px solid #f1f5f9">
                    <input type="checkbox" name="id_alimento[]" value="<?=$al['id']?>" id="al<?=$al['id']?>"
                           style="width:15px;height:15px;accent-color:var(--verde)"
                           onchange="togglePorcion(<?=$al['id']?>, this.checked)">
                    <label for="al<?=$al['id']?>" style="flex:1;font-size:13px;cursor:pointer">
                        <?=htmlspecialchars($al['nombre'])?>
                        <span style="font-size:11px;color:var(--gris)">(<?=$al['calorias']?> kcal · H:<?=$al['hierro_mg']?>mg · Ca:<?=$al['calcio_mg']?>mg)</span>
                    </label>
                    <input type="number" name="porcion_g[]" id="por<?=$al['id']?>" value="100" min="1" max="500" step="1"
                           style="width:70px;padding:4px 8px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:12px;display:none"
                           placeholder="g">
                    <span id="lbl<?=$al['id']?>" style="font-size:11px;color:var(--gris);display:none">g</span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalMenu').classList.remove('show')">Cancelar</button>
            <button type="submit" class="btn btn-primary">💾 Guardar y Calcular Nutrientes</button>
        </div>
    </form>
</div></div>

<script>
function togglePorcion(id, checked) {
    const por = document.getElementById('por'+id);
    const lbl = document.getElementById('lbl'+id);
    if (por) { por.style.display = checked ? 'block' : 'none'; }
    if (lbl) { lbl.style.display = checked ? 'inline' : 'none'; }
}
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
