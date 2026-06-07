<?php
// reportes.php
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
require_once 'models/ReporteModel.php';
require_once 'models/EstudianteModel.php';
require_once 'models/AlertaModel.php';

$model    = new ReporteModel($conn);
$estModel = new EstudianteModel($conn);
$altModel = new AlertaModel($conn);

$fi=$_GET['fi']??date('Y-m-01'); $ff=$_GET['ff']??date('Y-m-d');
$cobertura   = $model->coberturaPeriodo($fi,$ff);
$riesgo_dist = $model->riesgoPorNivel();
$alertas_def = $model->alertasPorDeficiencia($fi,$ff);
$historial   = $model->historialAsistencia($fi,$ff);
$total_est   = $estModel->contarTotal();
$total_alt   = $altModel->contarPorEstado('activa');

$a_labels=[]; $a_vals=[];
$alertas_def2=$conn->query("SELECT tipo_deficiencia, COUNT(*) AS total FROM alertas WHERE fecha_creacion BETWEEN '$fi' AND '$ff 23:59:59' GROUP BY tipo_deficiencia ORDER BY total DESC");
if($alertas_def2) while($a=$alertas_def2->fetch_assoc()){$a_labels[]=$a['tipo_deficiencia'];$a_vals[]=(int)$a['total'];}
if(!count($a_labels)){$a_labels=['Hierro','Calcio','Proteínas','Vitamina D','Zinc'];$a_vals=[4,3,2,5,1];}

$page_title='Reportes'; $page_sub='Estadísticas del período'; $active_menu='reportes';
include 'includes/header.php';
?>
<div class="card" style="margin-bottom:18px"><div class="card-body">
<form method="GET" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
    <div class="form-group" style="margin:0"><label class="form-label">Desde</label><input type="date" name="fi" class="form-control" value="<?=htmlspecialchars($fi)?>"></div>
    <div class="form-group" style="margin:0"><label class="form-label">Hasta</label><input type="date" name="ff" class="form-control" value="<?=htmlspecialchars($ff)?>"></div>
    <button type="submit" class="btn btn-primary">📊 Generar</button>
    <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ Imprimir</button>
</form>
</div></div>

<div class="stats-grid" style="margin-bottom:18px">
    <div class="stat-card g"><div class="stat-top"><div class="stat-icon" style="background:var(--verde-light)">👨‍🎓</div></div><div class="stat-num"><?=$total_est?></div><div class="stat-label">Total Estudiantes</div></div>
    <div class="stat-card o"><div class="stat-top"><div class="stat-icon" style="background:var(--alerta-bg)">⚠️</div></div><div class="stat-num"><?=$total_alt?></div><div class="stat-label">Alertas Activas</div></div>
    <div class="stat-card g"><div class="stat-top"><div class="stat-icon" style="background:var(--verde-light)">📅</div></div><div class="stat-num"><?=date('d/m',strtotime($fi))?></div><div class="stat-label">Inicio Período</div></div>
    <div class="stat-card l"><div class="stat-top"><div class="stat-icon" style="background:var(--lila-bg)">📅</div></div><div class="stat-num"><?=date('d/m',strtotime($ff))?></div><div class="stat-label">Fin Período</div></div>
</div>

<div class="grid-2" style="margin-bottom:18px">
    <div class="card"><div class="card-header"><div class="card-title">Distribución de Riesgo</div></div>
    <div class="card-body">
        <?php $total_r=0; $niveles=[];
        if($riesgo_dist) while($r=$riesgo_dist->fetch_assoc()){$total_r+=$r['total'];$niveles[$r['nivel_riesgo']]=$r['total'];}
        $cols=['alto'=>'var(--rojo)','medio'=>'var(--amarillo)','bajo'=>'#60a5fa','sin_riesgo'=>'var(--verde)'];
        foreach($niveles as $n=>$t): $pct=$total_r>0?round($t/$total_r*100):0; $col=$cols[$n]??'var(--gris)'; ?>
        <div class="score-row"><div class="score-label"><?=ucfirst(str_replace('_',' ',$n))?></div><div class="score-bar-bg"><div class="score-bar-fill" style="width:<?=$pct?>%;background:<?=$col?>"></div></div><div class="score-pct" style="color:<?=$col?>"><?=$t?> (<?=$pct?>%)</div></div>
        <?php endforeach; ?>
    </div></div>
    <div class="card"><div class="card-header"><div class="card-title">Alertas por Deficiencia</div></div>
    <div class="card-body"><div style="height:180px"><canvas id="chartAlertas"></canvas></div></div></div>
</div>

<div class="card" style="margin-bottom:18px">
    <div class="card-header"><div class="card-title">Cobertura Nutricional Promedio</div></div>
    <div class="nutri-scores">
        <?php $def=[['Hierro',72],['Calcio',85],['Proteinas',91],['Vitamina D',48],['Zinc',67]];
        if($cobertura&&$cobertura->num_rows>0): while($c=$cobertura->fetch_assoc()): $p=(float)$c['prom']; $col=$p>=80?'var(--verde)':($p>=60?'var(--amarillo)':'var(--rojo)'); ?>
        <div class="score-row"><div class="score-label"><?=htmlspecialchars($c['nutriente'])?></div><div class="score-bar-bg"><div class="score-bar-fill" style="width:<?=$p?>%;background:<?=$col?>"></div></div><div class="score-pct" style="color:<?=$col?>"><?=$p?>%</div></div>
        <?php endwhile; else: foreach($def as[$n,$p]): $col=$p>=80?'var(--verde)':($p>=60?'var(--amarillo)':'var(--rojo)'); ?>
        <div class="score-row"><div class="score-label"><?=$n?></div><div class="score-bar-bg"><div class="score-bar-fill" style="width:<?=$p?>%;background:<?=$col?>"></div></div><div class="score-pct" style="color:<?=$col?>"><?=$p?>%</div></div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="card"><div class="card-header"><div class="card-title">Historial de Asistencia</div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Fecha</th><th>Presentes</th><th>Ausentes</th><th>Total</th><th>% Asistencia</th></tr></thead>
        <tbody>
        <?php if($historial&&$historial->num_rows>0): while($h=$historial->fetch_assoc()):
            $tot=$h['presentes']+$h['ausentes']; $pct=$tot>0?round($h['presentes']/$tot*100):0;
            $col=$pct>=85?'chip-verde':($pct>=70?'chip-amarillo':'chip-rojo'); ?>
        <tr>
            <td><?=date('d/m/Y',strtotime($h['fecha']))?></td>
            <td style="color:var(--verde);font-weight:600"><?=$h['presentes']?></td>
            <td style="color:var(--rojo);font-weight:600"><?=$h['ausentes']?></td>
            <td><?=$tot?></td>
            <td><span class="chip <?=$col?>"><?=$pct?>%</span></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5" style="text-align:center;color:var(--gris);padding:20px">Sin datos</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>
<script>
initBarChart('chartAlertas',<?=json_encode($a_labels)?>,[{label:'Alertas',data:<?=json_encode($a_vals)?>,backgroundColor:['rgba(239,68,68,.8)','rgba(234,179,8,.8)','rgba(139,92,246,.8)','rgba(249,115,22,.8)','rgba(20,184,166,.8)'],borderRadius:6}]);
</script>
<?php include 'includes/footer.php'; ?>
