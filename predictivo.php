<?php
// predictivo.php — Punto de entrada MVP
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
require_once 'presenters/PredictivoPresenter.php';
require_once 'models/AlertaModel.php';

$presenter = new PredictivoPresenter($conn);

if(isset($_GET['recalcular'])){
    $presenter->ejecutarPrediccion();
    header('Location: predictivo.php?ok=1'); exit;
}

$estudiantes = $presenter->obtenerEstudiantesConScore();
$conteos     = $presenter->obtenerConteos();
$tendencia_q = $presenter->obtenerTendencia();
$t_fechas=[]; $t_altos=[]; $t_medios=[]; $t_bajos=[];
if($tendencia_q&&$tendencia_q->num_rows>0){
    while($t=$tendencia_q->fetch_assoc()){$t_fechas[]=$t['dia'];$t_altos[]=(int)$t['alto'];$t_medios[]=(int)$t['medio'];$t_bajos[]=(int)$t['bajo'];}
} else {$t_fechas=['Lun','Mar','Mié','Jue','Vie'];$t_altos=[3,4,2,5,3];$t_medios=[6,7,5,8,6];$t_bajos=[12,10,14,9,13];}

$det = null;
if(isset($_GET['est'])) $det = $presenter->obtenerDetalleEstudiante((int)$_GET['est']);

$page_title='Análisis Predictivo'; $page_sub='Motor de predicción nutricional'; $active_menu='predictivo';
include 'includes/header.php';
?>
<?php if(isset($_GET['ok'])): ?><div class="alert-box success">✅ Predicción ejecutada correctamente.</div><?php endif; ?>

<div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);border-radius:14px;padding:20px 26px;display:flex;align-items:center;gap:20px;margin-bottom:20px">
    <div style="font-size:36px">🧠</div>
    <div style="flex:1"><div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:17px;color:#fff;margin-bottom:4px">Motor de Análisis Predictivo</div><div style="color:rgba(255,255,255,.6);font-size:13px">Calcula el riesgo nutricional de cada estudiante con base en alertas, asistencia y cobertura del menú.</div></div>
    <a href="predictivo.php?recalcular=1" class="btn btn-primary" style="white-space:nowrap">⚡ Ejecutar Predicción</a>
</div>

<div class="stats-grid" style="margin-bottom:20px">
    <?php foreach(['alto'=>['🔴','Riesgo Alto','r'],'medio'=>['🟡','Riesgo Medio','o'],'bajo'=>['🟢','Riesgo Bajo','g'],'sin_riesgo'=>['✅','Sin Riesgo','g']] as $n=>[$ico,$lbl,$c]): ?>
    <div class="stat-card <?=$c?>"><div class="stat-top"><div class="stat-icon" style="background:var(--gris-light)"><?=$ico?></div></div><div class="stat-num"><?=$conteos[$n]?></div><div class="stat-label"><?=$lbl?></div></div>
    <?php endforeach; ?>
</div>

<div class="grid-2" style="margin-bottom:20px">
    <div class="card"><div class="card-header"><div class="card-title">Tendencia Semanal</div></div>
        <div class="card-body"><div style="height:170px"><canvas id="chartTendencia"></canvas></div></div>
    </div>
    <div class="card"><div class="card-header"><div class="card-title">Distribución Actual</div></div>
        <div class="card-body" style="display:flex;align-items:center;gap:16px">
            <div style="height:160px;width:160px;flex-shrink:0"><canvas id="chartDona"></canvas></div>
            <div>
                <?php $total_e=array_sum($conteos);
                foreach(['alto'=>['var(--rojo)','Alto'],'medio'=>['var(--amarillo)','Medio'],'bajo'=>'#60a5fa','sin_riesgo'=>['var(--verde)','Sin Riesgo']] as $n=>$v):
                    $col=is_array($v)?$v[0]:$v; $lbl=is_array($v)?$v[1]:ucfirst($n);
                    $pct=$total_e>0?round($conteos[$n]/$total_e*100):0; ?>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:9px">
                    <span style="width:12px;height:12px;border-radius:50%;background:<?=$col?>;flex-shrink:0"></span>
                    <span style="font-size:12.5px;font-weight:600;flex:1"><?=$lbl?></span>
                    <span style="font-size:12px;color:var(--gris)"><?=$conteos[$n]?> (<?=$pct?>%)</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-header"><div><div class="card-title">Scores por Estudiante</div></div><a href="predictivo.php?recalcular=1" class="btn btn-secondary btn-sm">🔄 Recalcular</a></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Estudiante</th><th>Grado</th><th>Score</th><th>Nivel</th><th>Alertas</th><th>Inasistencias</th><th></th></tr></thead>
        <tbody>
        <?php if($estudiantes&&$estudiantes->num_rows>0): while($e=$estudiantes->fetch_assoc()):
            $sc=$e['score']??'—'; $col=$sc==='—'?'#94a3b8':($sc>=70?'var(--rojo)':($sc>=40?'var(--amarillo)':($sc>=15?'#60a5fa':'var(--verde)'))); ?>
        <tr>
            <td><strong><?=htmlspecialchars($e['nombre'].' '.$e['apellido'])?></strong></td>
            <td><?=htmlspecialchars($e['grado'])?></td>
            <td><div style="display:flex;align-items:center;gap:8px"><div style="flex:1;height:6px;background:#e2e8f0;border-radius:20px;overflow:hidden;min-width:60px"><div style="height:100%;width:<?=$sc==='—'?0:$sc?>%;background:<?=$col?>;border-radius:20px"></div></div><span style="font-size:12px;font-weight:700;color:<?=$col?>"><?=$sc?></span></div></td>
            <td><span class="chip chip-<?=$e['nivel_riesgo']?>"><?=ucfirst(str_replace('_',' ',$e['nivel_riesgo']))?></span></td>
            <td><?=$e['alertas_act']>0?"<span class='chip chip-rojo'>{$e['alertas_act']}</span>":"<span style='color:var(--gris);font-size:12px'>0</span>"?></td>
            <td><?=$e['inasistencias']>0?"<span class='chip chip-amarillo'>{$e['inasistencias']}</span>":"<span style='color:var(--gris);font-size:12px'>0</span>"?></td>
            <td><a href="predictivo.php?est=<?=$e['id']?>#detalle" class="btn btn-secondary btn-sm">🔍 Ver</a></td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table></div>
</div>

<?php if($det): ?>
<div class="card" id="detalle">
    <div class="card-header"><div><div class="card-title">🔍 Detalle — <?=htmlspecialchars($det['nombre'].' '.$det['apellido'])?></div><div class="card-sub">Score: <?=$det['score_calc']?>/100</div></div><a href="predictivo.php#tabla" class="btn btn-secondary btn-sm">← Volver</a></div>
    <div class="card-body">
        <div class="grid-2">
            <div>
                <div style="font-size:14px;font-weight:600;margin-bottom:12px">Factores de Riesgo</div>
                <?php foreach($det['factores'] as $f):
                    $bg=$f['tipo']==='alto'?'var(--rojo-bg)':($f['tipo']==='medio'?'var(--amarillo-bg)':'var(--verde-light)');
                    $col=$f['tipo']==='alto'?'var(--rojo)':($f['tipo']==='medio'?'#92400e':'var(--verde-dark)');
                    $ico=$f['tipo']==='alto'?'🔴':($f['tipo']==='medio'?'🟡':'🟢'); ?>
                <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:<?=$bg?>;border-radius:10px;margin-bottom:8px">
                    <span><?=$ico?></span>
                    <div style="flex:1;font-size:13px;font-weight:600;color:<?=$col?>"><?=htmlspecialchars($f['desc'])?></div>
                    <span style="font-size:12px;font-weight:700;color:<?=$col?>"><?=$f['peso']>=0?'+'.$f['peso']:$f['peso']?> pts</span>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:14px;padding:16px;background:var(--gris-light);border-radius:12px;text-align:center">
                    <?php $niv_d=$det['score_calc']>=70?'alto':($det['score_calc']>=40?'medio':($det['score_calc']>=15?'bajo':'sin_riesgo')); ?>
                    <div style="font-size:42px;font-weight:800;color:<?=$det['score_calc']>=70?'var(--rojo)':($det['score_calc']>=40?'var(--amarillo)':($det['score_calc']>=15?'#60a5fa':'var(--verde)'))?>;"><?=$det['score_calc']?></div>
                    <div style="font-size:12px;color:var(--gris);margin:4px 0 8px">de 100 puntos</div>
                    <span class="chip chip-<?=$niv_d?>"><?=ucfirst(str_replace('_',' ',$niv_d))?></span>
                </div>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;margin-bottom:12px">Recomendaciones</div>
                <?php
                $recs=$det['score_calc']>=70?[['🩺','Remitir al servicio de salud del colegio urgentemente.'],['📞','Notificar a los padres inmediatamente.'],['🍽️','Diseñar menú personalizado con nutrientes deficientes.']]:($det['score_calc']>=40?[['👁️','Seguimiento semanal del estado nutricional.'],['🥗','Reforzar el menú con alimentos ricos en nutrientes.'],['📋','Registrar observaciones en cada tiempo de comida.']]:[['✅','Continuar con el plan nutricional actual.'],['📊','Monitoreo mensual de indicadores.']]);
                foreach($recs as[$ico,$txt]): ?>
                <div style="display:flex;gap:10px;padding:10px;background:#f8fafc;border-radius:9px;margin-bottom:8px;border-left:3px solid var(--verde)">
                    <span><?=$ico?></span><span style="font-size:13px"><?=$txt?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function(){
    const ctx=document.getElementById('chartTendencia');
    if(!ctx)return;
    new Chart(ctx,{type:'line',data:{labels:<?=json_encode($t_fechas)?>,datasets:[{label:'Alto',data:<?=json_encode($t_altos)?>,borderColor:'rgba(239,68,68,1)',backgroundColor:'rgba(239,68,68,.1)',tension:.4,fill:true,pointRadius:4},{label:'Medio',data:<?=json_encode($t_medios)?>,borderColor:'rgba(234,179,8,1)',backgroundColor:'rgba(234,179,8,.1)',tension:.4,fill:true,pointRadius:4},{label:'Bajo',data:<?=json_encode($t_bajos)?>,borderColor:'rgba(34,197,94,1)',backgroundColor:'rgba(34,197,94,.1)',tension:.4,fill:true,pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true}}}});
})();
(function(){
    const ctx=document.getElementById('chartDona');
    if(!ctx)return;
    new Chart(ctx,{type:'doughnut',data:{labels:['Alto','Medio','Bajo','Sin Riesgo'],datasets:[{data:[<?=implode(',',$conteos)?>],backgroundColor:['rgba(239,68,68,.85)','rgba(234,179,8,.85)','rgba(96,165,250,.85)','rgba(34,197,94,.85)'],borderWidth:2,borderColor:'#fff'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},cutout:'68%'}});
})();
</script>
<?php include 'includes/footer.php'; ?>
