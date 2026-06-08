<?php
// NutriBot Service — asistente. Sin BD propia: toma contexto de otros servicios.
// Responde con IA real si hay ANTHROPIC_API_KEY; si no, con un motor de
// intenciones basado en datos reales del sistema.
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'nutribot']);
if (!($method === 'POST' && $path === '/nutribot')) json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);

$in = body_json();
$mensaje = trim((string) ($in['mensaje'] ?? ''));
if ($mensaje === '') json_out(['error' => 'mensaje vacío'], 422);

// ── Contexto en tiempo real desde otros microservicios ──
$est   = svc_get('estudiantes', '/estudiantes')['data'] ?? [];
$total = count($est);
$riesgo = ['alto' => 0, 'medio' => 0, 'bajo' => 0, 'sin_riesgo' => 0];
foreach ($est as $e) { $k = $e['nivel_riesgo'] ?? 'sin_riesgo'; if (isset($riesgo[$k])) $riesgo[$k]++; }
$alConteos = svc_get('alertas', '/alertas/conteos')['data'] ?? [];
$activas   = (int) ($alConteos['activa'] ?? 0);
$cob = svc_get('reportes', '/reportes/cobertura')['data'] ?? [];
$prom = $cob ? round(array_sum(array_map(fn($c) => (float) $c['prom'], $cob)) / count($cob)) : 0;
$bajos = array_values(array_filter($cob, fn($c) => (float) $c['prom'] < 70));
$asis = svc_get('asistencia', '/asistencia/resumen')['data'] ?? [];

$apiKey = (string) env_('ANTHROPIC_API_KEY', '');

// ── Si hay clave de IA, usar Claude con el contexto real ──
if ($apiKey !== '') {
    $ctx = "Estudiantes: $total. Riesgo alto: {$riesgo['alto']}, medio: {$riesgo['medio']}, bajo: {$riesgo['bajo']}. "
         . "Alertas activas: $activas. Cobertura nutricional media: {$prom}%.";
    $system = "Eres NutriBot, asistente de NutriPredict Escolar. Responde en español, claro y breve, "
            . "orientado a nutrición escolar. Usa estos datos reales cuando apliquen: $ctx";
    $payload = json_encode(['model' => env_('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'), 'max_tokens' => 600,
        'system' => $system, 'messages' => [['role' => 'user', 'content' => $mensaje]]]);
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'anthropic-version: 2023-06-01', 'x-api-key: ' . $apiKey], CURLOPT_TIMEOUT => 30]);
    $resp = curl_exec($ch); $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($resp !== false && $code === 200) {
        $data = json_decode($resp, true);
        json_out(['respuesta' => $data['content'][0]['text'] ?? 'Sin respuesta.', 'fuente' => 'claude']);
    }
}

// ── Motor de intenciones (sin IA) basado en datos reales ──
function norm(string $s): string {
    $s = mb_strtolower($s);
    return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']);
}
function has(string $m, array $w): bool { foreach ($w as $x) if (str_contains($m, $x)) return true; return false; }

$m = norm($mensaje);
$SUG = ['Hierro'=>'lentejas, espinaca o hígado','Calcio'=>'leche, yogur o queso','Proteinas'=>'huevo, pollo o atún',
        'Vitamina D'=>'huevo, sardina o leche fortificada','Zinc'=>'carne, pollo o frijoles'];
$r = '';

if (has($m, ['hola','buenas','buenos dias','buenas tardes','hey','saludos'])) {
    $r = "¡Hola! 👋 Soy **NutriBot**. Ahora mismo hay **$total estudiantes**, **$activas alertas activas** y una cobertura media del **{$prom}%**. Pregúntame por *riesgo*, *alertas*, *cobertura*, *asistencia* o pídeme *recomendaciones*.";
} elseif (has($m, ['ayuda','que puedes','qué puedes','que haces','funcion','para que sirves','opciones'])) {
    $r = "Puedo ayudarte con:\n• **Riesgo** de los estudiantes (alto/medio/bajo)\n• **Alertas** nutricionales activas\n• **Cobertura** nutricional vs ICBF\n• **Asistencia** del día\n• **Recomendaciones** según las deficiencias\nEscribe tu pregunta con naturalidad. 😊";
} elseif (has($m, ['riesgo alto','en riesgo alto','alto riesgo'])) {
    $r = $riesgo['alto'] > 0
        ? "🔴 Hay **{$riesgo['alto']} estudiante(s) en riesgo alto**. Te recomiendo revisarlos en *Predictivo* y atender sus alertas primero."
        : "✅ No hay estudiantes en **riesgo alto** en este momento. (Medio: {$riesgo['medio']}, Bajo: {$riesgo['bajo']}).";
} elseif (has($m, ['riesgo','predict','prediccion','modelo','score','puntaje'])) {
    $r = "📊 Distribución de riesgo: **Alto {$riesgo['alto']} · Medio {$riesgo['medio']} · Bajo {$riesgo['bajo']} · Sin riesgo {$riesgo['sin_riesgo']}**.\nEl puntaje (0–100) combina alertas activas, inasistencias y cobertura del menú. Puedes recalcularlo en *Predictivo → Recalcular riesgo*.";
} elseif (has($m, ['alerta'])) {
    $r = "🔔 Hay **$activas alerta(s) activa(s)**. Revísalas en el módulo *Alertas*; puedes marcarlas como resueltas cuando se atiendan.";
} elseif (has($m, ['cuantos estudiantes','numero de estudiantes','total de estudiantes','cuantos hay','estudiantes hay'])) {
    $r = "👥 Hay **$total estudiantes** registrados. Por riesgo: alto {$riesgo['alto']}, medio {$riesgo['medio']}, bajo {$riesgo['bajo']}, sin riesgo {$riesgo['sin_riesgo']}.";
} elseif (has($m, ['asistencia','asistieron','presentes','ausentes','faltaron'])) {
    $p = (int)($asis['presentes']??0); $a = (int)($asis['ausentes']??0); $t = (int)($asis['total']??0);
    $r = $t > 0 ? "✅ Asistencia de hoy: **$p presentes** y **$a ausentes** de $t registrados." : "Aún no hay asistencia registrada hoy. Puedes registrarla en el módulo *Asistencia*.";
} elseif (has($m, ['cobertura','nutricion','nutrición','menu','menú','icbf','vitamina','hierro','calcio','proteina','zinc'])) {
    $detalle = $bajos ? ' Por debajo del 70%: ' . implode(', ', array_map(fn($c) => $c['nutriente'] . " ({$c['prom']}%)", $bajos)) . '.' : ' Todos los nutrientes están en buen nivel. ✅';
    $r = "🥗 La cobertura nutricional media es **{$prom}%** del recomendado por el ICBF.$detalle";
} elseif (has($m, ['recomenda','consejo','mejorar','sugerencia','que hago','que debo'])) {
    if ($bajos) {
        $lineas = array_map(function ($c) use ($SUG) { $n = $c['nutriente']; return "• **$n** ({$c['prom']}%): incluye " . ($SUG[$n] ?? 'alimentos ricos en ese nutriente') . '.'; }, $bajos);
        $r = "Para mejorar la nutrición, refuerza los nutrientes bajos:\n" . implode("\n", $lineas);
    } else {
        $r = "La cobertura está en buen nivel ({$prom}%). Mantén menús variados con proteínas, lácteos, frutas y verduras, y vigila a los estudiantes con alertas activas.";
    }
} elseif (has($m, ['imc','peso','talla','indice de masa'])) {
    $r = "El **IMC** se calcula como peso(kg) / talla(m)². El sistema lo calcula automáticamente al registrar un estudiante y lo clasifica (Bajo peso, Normal, Sobrepeso, Obesidad) según las tablas OMS para niños.";
} elseif (has($m, ['gracias','muchas gracias','thanks'])) {
    $r = "¡Con gusto! 😊 Si necesitas algo más sobre nutrición, riesgo o alertas, aquí estoy.";
} else {
    $r = "No estoy seguro de haber entendido. 🤔 Puedo contarte sobre **riesgo**, **alertas**, **cobertura nutricional**, **asistencia** o darte **recomendaciones**. Por ejemplo: *“¿cuántos están en riesgo alto?”* o *“dame recomendaciones”*.";
}

json_out(['respuesta' => $r, 'fuente' => 'asistente']);
