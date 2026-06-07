<?php
// ayuda.php — Centro de ayuda y documentación del sistema
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';

$page_title  = 'Centro de Ayuda';
$page_sub    = 'Guía de uso y documentación del sistema';
$active_menu = 'ayuda';
include 'includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);border-radius:14px;padding:24px 28px;margin-bottom:22px;position:relative;overflow:hidden">
    <div style="position:absolute;right:-30px;top:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(34,197,94,.15) 0%,transparent 70%);border-radius:50%"></div>
    <div style="position:relative;z-index:1">
        <div style="font-size:32px;margin-bottom:8px">📚</div>
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:20px;color:#fff;margin-bottom:6px">Centro de Ayuda — NutriPredict Escolar</div>
        <div style="color:rgba(255,255,255,.6);font-size:13.5px;max-width:680px">Encuentra aquí información completa sobre cada módulo del sistema, cómo funciona el motor predictivo y recomendaciones de uso.</div>
    </div>
</div>

<!-- ACCESOS RÁPIDOS -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
    <?php
    $modulos = [
        ['🏠','Panel Principal',    'Resumen y métricas clave','#22c55e'],
        ['👥','Estudiantes',        'Gestión del padrón','#8b5cf6'],
        ['🧠','Análisis Predictivo','Motor de riesgo nutricional','#ef4444'],
        ['🔔','Alertas',            'Deficiencias detectadas','#f97316'],
        ['📋','Menús del Día',      'Planificación alimentaria','#0ea5e9'],
        ['✅','Asistencia',         'Control del comedor','#14b8a6'],
        ['📄','Reportes',           'Estadísticas y tendencias','#eab308'],
        ['👤','Usuarios y Roles',   'Permisos del sistema','#64748b'],
    ];
    foreach ($modulos as [$ico, $tit, $sub, $color]):
    ?>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1.5px solid #e2e8f0;cursor:pointer;transition:.2s" onmouseover="this.style.borderColor='<?=$color?>';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#e2e8f0';this.style.transform='none'">
        <div style="font-size:24px;margin-bottom:8px"><?=$ico?></div>
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:13px;margin-bottom:3px"><?=$tit?></div>
        <div style="font-size:11.5px;color:var(--gris)"><?=$sub?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid-2" style="gap:20px">

    <!-- COLUMNA IZQUIERDA -->
    <div>
        <!-- Módulo Predictivo (destaque) -->
        <div class="card" style="margin-bottom:18px">
            <div class="card-header" style="background:linear-gradient(135deg,#0f172a,#1e3a8a);border-radius:12px 12px 0 0">
                <div>
                    <div class="card-title" style="color:#fff">🧠 Cómo funciona el Motor Predictivo</div>
                    <div class="card-sub" style="color:rgba(255,255,255,.5)">Algoritmo de scoring nutricional</div>
                </div>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:var(--gris);margin-bottom:16px">El motor asigna un <strong>score de 0 a 100</strong> a cada estudiante evaluando factores de riesgo. A mayor puntaje, mayor riesgo nutricional.</p>

                <div style="margin-bottom:14px">
                    <div style="font-size:12.5px;font-weight:700;margin-bottom:10px;color:var(--azul-dark)">⚖️ Factores evaluados:</div>
                    <?php
                    $factores = [
                        ['🔴','Alertas activas (≥3)','hasta +40 pts','var(--rojo-bg)','var(--rojo)'],
                        ['🟡','Alertas activas (1-2)','hasta +25 pts','var(--amarillo-bg)','#92400e'],
                        ['📅','Inasistencias ≥5 en 10 días','hasta +20 pts','var(--alerta-bg)','var(--alerta)'],
                        ['🥗','Cobertura nutricional baja (<60%)','hasta +30 pts','var(--rojo-bg)','var(--rojo)'],
                        ['✅','Alertas resueltas (factor protector)','-10 pts','var(--verde-light)','var(--verde-dark)'],
                    ];
                    foreach ($factores as [$ico, $desc, $peso, $bg, $col]):
                    ?>
                    <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:<?=$bg?>;border-radius:9px;margin-bottom:7px">
                        <span style="font-size:15px"><?=$ico?></span>
                        <span style="flex:1;font-size:12.5px;font-weight:600;color:<?=$col?>"><?=$desc?></span>
                        <span style="font-size:12px;font-weight:800;color:<?=$col?>"><?=$peso?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="font-size:12.5px;font-weight:700;margin-bottom:10px;color:var(--azul-dark)">📊 Niveles de riesgo:</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <?php
                    $niveles = [
                        ['Sin Riesgo','0-14','var(--verde-light)','var(--verde-dark)'],
                        ['Riesgo Bajo','15-39','#dbeafe','#1e40af'],
                        ['Riesgo Medio','40-69','var(--amarillo-bg)','#92400e'],
                        ['Riesgo Alto','70-100','var(--rojo-bg)','var(--rojo)'],
                    ];
                    foreach ($niveles as [$lbl, $rng, $bg, $col]):
                    ?>
                    <div style="padding:8px 12px;background:<?=$bg?>;border-radius:9px;text-align:center">
                        <div style="font-size:11px;font-weight:700;color:<?=$col?>"><?=$lbl?></div>
                        <div style="font-size:10px;color:<?=$col?>;opacity:.8"><?=$rng?> pts</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Roles y permisos -->
        <div class="card" style="margin-bottom:18px">
            <div class="card-header">
                <div><div class="card-title">👤 Roles y Permisos</div><div class="card-sub">Qué puede hacer cada perfil</div></div>
            </div>
            <div class="card-body">
                <?php
                $roles = [
                    ['🛡️','Administrador','#22c55e','Acceso total al sistema. Gestiona usuarios, roles y toda la información.'],
                    ['🏫','Directora','#0ea5e9','Vista de reportes, dashboard, alertas y análisis predictivo. Sin edición.'],
                    ['📚','Docente','#8b5cf6','Consulta de estudiantes de su grado, alertas y asistencia. Solo lectura.'],
                    ['👨‍🍳','Encargado Restaurante','#f97316','Gestión de menús, alimentos, asistencia y alertas nutricionales.'],
                ];
                foreach ($roles as [$ico, $nombre, $color, $desc]):
                ?>
                <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9">
                    <div style="width:38px;height:38px;border-radius:10px;background:<?=$color?>20;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0"><?=$ico?></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--azul-dark);margin-bottom:3px"><?=$nombre?></div>
                        <div style="font-size:12px;color:var(--gris)"><?=$desc?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA -->
    <div>
        <!-- Guías rápidas -->
        <div class="card" style="margin-bottom:18px">
            <div class="card-header">
                <div><div class="card-title">🚀 Guías Rápidas</div><div class="card-sub">Tareas frecuentes paso a paso</div></div>
            </div>
            <div class="card-body" style="padding:10px 16px">
                <?php
                $guias = [
                    ['Registrar un nuevo estudiante', [
                        'Ir al módulo <strong>Estudiantes</strong>',
                        'Clic en "➕ Nuevo Estudiante"',
                        'Completar nombre, apellido, grado y fecha de nacimiento',
                        'Guardar — el sistema lo activa automáticamente'
                    ]],
                    ['Ejecutar el análisis predictivo', [
                        'Ir a <strong>Análisis Predictivo</strong>',
                        'Clic en "⚡ Ejecutar Predicción"',
                        'El motor recalcula todos los scores en segundos',
                        'Los estudiantes de riesgo alto generan alerta automática'
                    ]],
                    ['Registrar asistencia diaria', [
                        'Ir al módulo <strong>Asistencia</strong>',
                        'Seleccionar la fecha (por defecto hoy)',
                        'Marcar asistencia estudiante por estudiante o en bloque',
                        'El sistema actualiza las métricas del dashboard'
                    ]],
                    ['Crear una alerta manual', [
                        'Ir al módulo <strong>Alertas</strong>',
                        'Clic en "➕ Nueva Alerta"',
                        'Seleccionar estudiante, tipo de deficiencia y severidad',
                        'La alerta queda activa hasta que se marque como resuelta'
                    ]],
                ];
                foreach ($guias as [$titulo, $pasos]):
                ?>
                <details style="margin-bottom:10px;border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden">
                    <summary style="padding:11px 14px;font-size:13px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;background:#f8fafc">
                        <?=$titulo?>
                        <span style="font-size:11px;color:var(--gris)">▼</span>
                    </summary>
                    <div style="padding:12px 14px;background:#fff">
                        <ol style="margin:0;padding-left:18px">
                            <?php foreach ($pasos as $i => $paso): ?>
                            <li style="font-size:12.5px;color:var(--gris);margin-bottom:6px;line-height:1.5"><?=$paso?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recomendaciones nutricionales -->
        <div class="card" style="margin-bottom:18px">
            <div class="card-header">
                <div><div class="card-title">🥗 Referencia Nutricional Escolar</div><div class="card-sub">Requerimientos diarios recomendados (OMS/Colombia)</div></div>
            </div>
            <div class="card-body">
                <?php
                $nutrientes = [
                    ['Calorías totales', '1.600–2.000 kcal', '🔥', '#f97316'],
                    ['Proteínas', '45–55 g/día', '💪', '#8b5cf6'],
                    ['Hierro', '8–15 mg/día', '🩸', '#ef4444'],
                    ['Calcio', '700–1.300 mg/día', '🦴', '#eab308'],
                    ['Vitamina D', '10–15 µg/día', '☀️', '#f59e0b'],
                    ['Zinc', '5–11 mg/día', '🔬', '#14b8a6'],
                    ['Vitamina C', '25–65 mg/día', '🍊', '#22c55e'],
                ];
                foreach ($nutrientes as [$nombre, $valor, $ico, $color]):
                ?>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f8fafc">
                    <span style="font-size:16px"><?=$ico?></span>
                    <span style="flex:1;font-size:12.5px;font-weight:600"><?=$nombre?></span>
                    <span style="font-size:12px;font-weight:700;color:<?=$color?>;background:<?=$color?>15;padding:3px 10px;border-radius:20px"><?=$valor?></span>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:12px;padding:10px;background:var(--verde-light);border-radius:9px;font-size:11.5px;color:var(--verde-dark)">
                    📌 Valores orientativos para estudiantes de 6-14 años según guías de la OMS y el ICBF Colombia.
                </div>
            </div>
        </div>

        <!-- Info técnica -->
        <div class="card">
            <div class="card-header">
                <div><div class="card-title">⚙️ Información Técnica</div><div class="card-sub">Arquitectura y tecnologías</div></div>
            </div>
            <div class="card-body">
                <?php
                $tech = [
                    ['🏗️','Patrón de diseño','MVP (Model-View-Presenter)'],
                    ['🐘','Backend','PHP 8+ con MySQLi'],
                    ['🎨','Frontend','CSS3 + Chart.js 4.4 + Google Fonts'],
                    ['🤖','Asistente Virtual','Claude AI (Anthropic)'],
                    ['📊','Gráficas','Chart.js (líneas, barras, dona)'],
                    ['🔐','Autenticación','Sesiones PHP + control de roles'],
                    ['🗄️','Base de datos','MySQL / MariaDB'],
                ];
                foreach ($tech as [$ico, $nombre, $valor]):
                ?>
                <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid #f8fafc">
                    <span style="font-size:15px"><?=$ico?></span>
                    <span style="flex:1;font-size:12px;color:var(--gris)"><?=$nombre?></span>
                    <span style="font-size:12px;font-weight:600"><?=$valor?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
