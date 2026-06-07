<?php
// NutriBot Service — asistente IA. Sin BD propia: toma contexto de otros servicios.
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'nutribot']);
if (!($method === 'POST' && $path === '/nutribot')) json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);

$in = body_json();
$mensaje = trim((string) ($in['mensaje'] ?? ''));
if ($mensaje === '') json_out(['error' => 'mensaje vacío'], 422);

// Contexto en tiempo real desde otros microservicios
$riesgo  = svc_get('predictivo', '/predictivo/conteos')['data'] ?? [];
$alertas = svc_get('alertas', '/alertas/conteos')['data'] ?? [];
$enRiesgoAlto = (int) ($riesgo['alto'] ?? 0);
$alertasActivas = (int) ($alertas['activa'] ?? 0);

$apiKey = (string) env_('ANTHROPIC_API_KEY', '');
$modelo = (string) env_('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514');

$system = "Eres NutriBot, asistente del sistema NutriPredict Escolar. Contexto actual: "
    . "$alertasActivas alertas activas, $enRiesgoAlto estudiantes en riesgo alto. "
    . "Responde en español, claro y conciso, con orientación nutricional escolar.";

if ($apiKey !== '') {
    $payload = json_encode(['model' => $modelo, 'max_tokens' => 600, 'system' => $system,
        'messages' => [['role' => 'user', 'content' => $mensaje]]]);
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'anthropic-version: 2023-06-01', 'x-api-key: ' . $apiKey],
        CURLOPT_TIMEOUT => 30]);
    $resp = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp !== false && $code === 200) {
        $data = json_decode($resp, true);
        json_out(['respuesta' => $data['content'][0]['text'] ?? 'Sin respuesta.', 'fuente' => 'claude']);
    }
}

// Respaldo sin IA (basado en datos reales)
$m = strtolower($mensaje);
if (str_contains($m, 'riesgo')) $r = "🔴 Hay $enRiesgoAlto estudiantes en riesgo alto. Revisa el módulo Predictivo.";
elseif (str_contains($m, 'alerta')) $r = "📊 Hay $alertasActivas alertas activas. Revisa el módulo Alertas.";
else $r = "Soy NutriBot 🤖. Actualmente: $alertasActivas alertas activas y $enRiesgoAlto en riesgo alto. ¿En qué te ayudo?";
json_out(['respuesta' => $r, 'fuente' => 'fallback']);
