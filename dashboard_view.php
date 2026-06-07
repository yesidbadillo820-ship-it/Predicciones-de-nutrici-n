<?php
// views/dashboard_view.php
// VISTA: Solo muestra datos. No contiene lógica ni consultas.
?>

<div class="welcome-banner">
    <div class="welcome-text">
        <h2>¡Buenos días, <?=htmlspecialchars(getUsuario()['nombre'])?>! 👋</h2>
        <p><?=$resumen['total_alertas']>0
            ?"Hay <strong style='color:var(--verde)'>{$resumen['total_alertas']} alerta(s) activa(s)</strong> que requieren atención."
            :"<strong style='color:var(--verde)'>Sin alertas activas.</strong> ¡Todo bajo control!"?></p>
    </div>
    <div class="welcome-stats">
        <div class="w-stat"><div class="num"><?=$resumen['total_estudiantes']?></div><div class="lbl">Estudiantes</div></div>
        <div class="w-stat"><div class="num">94.2%</div><div class="lbl">Precisión</div></div>
        <div class="w-stat"><div class="num"><?=$resumen['asistencia_hoy']?></div><div class="lbl">Asistieron</div></div>
        <div class="w-stat"><div class="num">89%</div><div class="lbl">Óptima Nutr.</div></div>
    </div>
</div>

<!-- TARJETAS ESTADÍSTICAS -->
<div class="stats-grid">
    <div class="stat-card g" onclick="location='estudiantes.php'">
        <div class="stat-top"><div class="stat-icon" style="background:var(--verde-light)">👨‍🎓</div><span class="stat-badge" style="background:var(--verde-light);color:var(--verde-dark)">Activos</span></div>
        <div class="stat-num"><?=$resumen['total_estudiantes']?></div><div class="stat-label">Total Estudiantes</div>
    </div>
    <div class="stat-card o" onclick="location='alertas.php'">
        <div class="stat-top"><div class="stat-icon" style="background:var(--alerta-bg)">⚠️</div><span class="stat-badge" style="background:var(--amarillo-bg);color:#92400e">Activas</span></div>
        <div class="stat-num"><?=$resumen['total_alertas']?></div><div class="stat-label">Alertas Activas</div>
    </div>
    <div class="stat-card r" onclick="location='predictivo.php'">
        <div class="stat-top"><div class="stat-icon" style="background:var(--rojo-bg)">🔴</div><span class="stat-badge" style="background:var(--rojo-bg);color:var(--rojo)">Riesgo</span></div>
        <div class="stat-num"><?=$resumen['en_riesgo']?></div><div class="stat-label">En Riesgo Nutricional</div>
    </div>
    <div class="stat-card l" onclick="location='asistencia.php'">
        <div class="stat-top"><div class="stat-icon" style="background:var(--lila-bg)">🍽️</div><span class="stat-badge" style="background:var(--verde-light);color:var(--verde-dark)">Hoy</span></div>
        <div class="stat-num"><?=$resumen['asistencia_hoy']?></div><div class="stat-label">Asistencia Hoy</div>
    </div>
</div>

<!-- GRÁFICA + ALERTAS -->
<div class="mid-grid">
    <div class="card">
        <div class="card-header">
            <div><div class="card-title">Riesgo Nutricional Semanal</div><div class="card-sub">Últimos 7 días</div></div>
            <a href="reportes.php" class="btn btn-secondary btn-sm">Ver Reporte</a>
        </div>
        <div class="card-body">
            <div style="display:flex;gap:14px;margin-bottom:10px">
                <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--gris)"><span style="width:10px;height:10px;border-radius:50%;background:var(--rojo);display:inline-block"></span>Alto</span>
                <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--gris)"><span style="width:10px;height:10px;border-radius:50%;background:var(--amarillo);display:inline-block"></span>Medio</span>
                <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--gris)"><span style="width:10px;height:10px;border-radius:50%;background:var(--verde);display:inline-block"></span>Bajo</span>
            </div>
            <div style="height:160px"><canvas id="chartRiesgo"></canvas></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div><div class="card-title">Alertas Recientes</div><div class="card-sub">Deficiencias detectadas</div></div>
            <a href="alertas.php" class="btn btn-secondary btn-sm">Ver Todas</a>
        </div>
        <div class="alerts-list">
            <?php if($alertas&&$alertas->num_rows>0): while($a=$alertas->fetch_assoc()):
                $iconos=['Hierro'=>['🩸','var(--rojo-bg)'],'Calcio'=>['🦴','var(--amarillo-bg)'],'Proteinas'=>['💪','var(--lila-bg)'],'Vitamina D'=>['☀️','var(--alerta-bg)'],'Zinc'=>['🔬','var(--teal-bg)'],'Riesgo General'=>['⚠️','var(--alerta-bg)']];
                $ico=$iconos[$a['tipo_deficiencia']]??['⚠️','var(--alerta-bg)'];
            ?>
            <div class="alert-item">
                <div class="alert-dot-wrap" style="background:<?=$ico[1]?>"><?=$ico[0]?></div>
                <div class="alert-info">
                    <div class="title">Déficit <?=htmlspecialchars($a['tipo_deficiencia'])?></div>
                    <div class="detail"><?=htmlspecialchars($a['nombre'].' '.$a['apellido'])?> · <?=htmlspecialchars($a['grado'])?></div>
                </div>
                <div class="alert-time"><?=DashboardPresenter::tiempoRelativo($a['fecha_creacion'])?></div>
            </div>
            <?php endwhile; else: ?>
            <div class="empty-state"><div class="icon">✅</div><p>Sin alertas activas</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BOTTOM -->
<div class="grid-3">
    <div class="card">
        <div class="card-header">
            <div><div class="card-title">Estudiantes en Riesgo</div></div>
            <a href="estudiantes.php" class="btn btn-secondary btn-sm">Ver Todos</a>
        </div>
        <div class="table-wrap"><table>
            <thead><tr><th>Nombre</th><th>Grado</th><th>Riesgo</th></tr></thead>
            <tbody>
            <?php
            $cnt = 0;
            $estudiantes->data_seek(0);
            while ($cnt < 6 && ($e = $estudiantes->fetch_assoc())):
                if (!in_array($e['nivel_riesgo'], ['alto','medio'])) continue;
                $cnt++;
            ?>
            <tr>
                <td><strong><?=htmlspecialchars($e['nombre'].' '.$e['apellido'])?></strong></td>
                <td><?=htmlspecialchars($e['grado'])?></td>
                <td><span class="chip chip-<?=$e['nivel_riesgo']?>"><?=ucfirst($e['nivel_riesgo'])?></span></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div><div class="card-title">Menú de Hoy</div><div class="card-sub"><?=date('d \d\e F, Y')?></div></div>
            <a href="menus.php" class="btn btn-secondary btn-sm">Editar</a>
        </div>
        <div class="menu-day">
            <?php
            $ti=['desayuno'=>'🌅','almuerzo'=>'☀️','merienda'=>'🌙'];
            if($menu_hoy&&$menu_hoy->num_rows>0): while($m=$menu_hoy->fetch_assoc()):
                $nuts=json_decode($m['nutrientes_cubre'],true)??[];
            ?>
            <div class="menu-meal">
                <div class="meal-type"><?=($ti[$m['tipo_tiempo']]??'🍽️')?> <?=ucfirst($m['tipo_tiempo'])?></div>
                <div class="meal-title"><?=htmlspecialchars($m['descripcion'])?></div>
                <div class="nutrients">
                    <?php foreach($nuts as $nt): ?>
                    <span class="nutrient-tag" style="<?=$nt['ok']?'background:var(--verde-light);color:var(--verde-dark)':'background:var(--rojo-bg);color:var(--rojo)'?>">
                        <?=htmlspecialchars($nt['nombre'])?> <?=$nt['ok']?'✓':'✗'?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="empty-state"><div class="icon">🍽️</div><p>Sin menú registrado hoy</p><a href="menus.php" class="btn btn-primary btn-sm" style="margin-top:10px">Registrar</a></div>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header"><div class="card-title">Cobertura Nutricional</div><div class="card-sub">Últimos 7 días</div></div>
            <div class="nutri-scores">
                <?php
                $def=[['Hierro',72],['Calcio',85],['Proteinas',91],['Vitamina D',48],['Zinc',67]];
                if($cobertura&&$cobertura->num_rows>0):
                    while($c=$cobertura->fetch_assoc()): $p=(float)$c['prom']; $col=$p>=80?'var(--verde)':($p>=60?'var(--amarillo)':'var(--rojo)');
                ?>
                <div class="score-row"><div class="score-label"><?=htmlspecialchars($c['nutriente'])?></div><div class="score-bar-bg"><div class="score-bar-fill" style="width:<?=$p?>%;background:<?=$col?>"></div></div><div class="score-pct" style="color:<?=$col?>"><?=$p?>%</div></div>
                <?php endwhile; else: foreach($def as[$n,$p]): $col=$p>=80?'var(--verde)':($p>=60?'var(--amarillo)':'var(--rojo)'); ?>
                <div class="score-row"><div class="score-label"><?=$n?></div><div class="score-bar-bg"><div class="score-bar-fill" style="width:<?=$p?>%;background:<?=$col?>"></div></div><div class="score-pct" style="color:<?=$col?>"><?=$p?>%</div></div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">Acciones Rápidas</div></div>
            <div class="quick-actions">
                <button class="qa-btn" onclick="location='estudiantes.php?accion=nuevo'"><div class="qa-icon" style="background:var(--verde-light)">➕</div><div class="qa-label">Nuevo Estudiante</div></button>
                <button class="qa-btn" onclick="location='menus.php?accion=nuevo'"><div class="qa-icon" style="background:var(--lila-bg)">📋</div><div class="qa-label">Registrar Menú</div></button>
                <button class="qa-btn" onclick="location='asistencia.php'"><div class="qa-icon" style="background:var(--teal-bg)">✅</div><div class="qa-label">Asistencia</div></button>
                <button class="qa-btn" onclick="location='reportes.php'"><div class="qa-icon" style="background:var(--alerta-bg)">📄</div><div class="qa-label">Reporte</div></button>
            </div>
        </div>
    </div>
</div>

<script>
initBarChart('chartRiesgo', <?=json_encode($tendencia['dias'])?>, [
    {label:'Alto',      data:<?=json_encode($tendencia['altos'])?>,  backgroundColor:'rgba(239,68,68,0.82)',  borderRadius:5,borderSkipped:false},
    {label:'Medio',     data:<?=json_encode($tendencia['medios'])?>, backgroundColor:'rgba(234,179,8,0.82)',  borderRadius:5,borderSkipped:false},
    {label:'Sin Riesgo',data:<?=json_encode($tendencia['bajos'])?>,  backgroundColor:'rgba(34,197,94,0.82)',  borderRadius:5,borderSkipped:false}
]);
</script>
