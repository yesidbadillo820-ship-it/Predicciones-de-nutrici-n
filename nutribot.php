<?php
// nutribot.php — Asistente Virtual NutriBot (powered by Claude AI)
// Endpoint AJAX: recibe mensajes del chat y devuelve respuestas
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';

header('Content-Type: application/json');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($input['mensaje'] ?? '');
$historial = $input['historial'] ?? [];

if (empty($mensaje)) {
    echo json_encode(['error' => 'Mensaje vacío']);
    exit;
}

// ── Recopilar contexto real del sistema ──────────────────────────
$total_estudiantes = $conn->query("SELECT COUNT(*) AS t FROM estudiantes WHERE activo=1")->fetch_assoc()['t'] ?? 0;
$alertas_activas   = $conn->query("SELECT COUNT(*) AS t FROM alertas WHERE estado='activa'")->fetch_assoc()['t'] ?? 0;
$en_riesgo         = $conn->query("SELECT COUNT(*) AS t FROM estudiantes WHERE nivel_riesgo='alto' AND activo=1")->fetch_assoc()['t'] ?? 0;
$asistencia_hoy    = $conn->query("SELECT COUNT(*) AS t FROM asistencia WHERE fecha=CURDATE() AND asistio=1")->fetch_assoc()['t'] ?? 0;

$usuario = getUsuario();

// ── Construir system prompt con contexto real ────────────────────
$system_prompt = "Eres NutriBot, el asistente virtual inteligente de NutriPredict Escolar, un sistema de gestión y predicción nutricional para instituciones educativas colombianas.

CONTEXTO ACTUAL DEL SISTEMA (datos reales en tiempo real):
- Total de estudiantes activos: $total_estudiantes
- Alertas nutricionales activas: $alertas_activas
- Estudiantes en riesgo alto: $en_riesgo
- Asistencia hoy al comedor: $asistencia_hoy
- Usuario conectado: {$usuario['nombre']} (Rol: {$usuario['rol']})

MÓDULOS DEL SISTEMA:
1. Dashboard — Panel principal con resumen general
2. Estudiantes — Gestión de estudiantes (registro, edición, historial)
3. Menús del Día — Planificación de menús escolares
4. Catálogo Alimentos — Base de datos de alimentos con valores nutricionales
5. Asistencia — Registro de asistencia al comedor escolar
6. Alertas — Alertas de deficiencias nutricionales detectadas
7. Análisis Predictivo — Motor de IA que calcula scores de riesgo nutricional (0-100)
8. Reportes — Generación de reportes estadísticos
9. Usuarios y Roles — Gestión de usuarios (admin, encargado_restaurante, docente, directora)

MODELO PREDICTIVO:
El sistema usa un algoritmo de scoring que evalúa:
- Alertas activas del estudiante (peso: hasta 40 pts)
- Inasistencias en los últimos 10 días (peso: hasta 20 pts)
- Cobertura nutricional del menú (peso: hasta 30 pts)
- Factor protector: alertas resueltas (-10 pts)
Niveles: Sin Riesgo (0-14), Bajo (15-39), Medio (40-69), Alto (70-100)

INSTRUCCIONES:
- Responde siempre en español, de forma amigable, clara y profesional
- Cuando el usuario pregunte por datos del sistema, usa los datos reales proporcionados arriba
- Puedes dar recomendaciones nutricionales, explicar funcionalidades del sistema o ayudar a interpretar los datos
- Si no sabes algo específico, sugiere al usuario revisar el módulo correspondiente
- Mantén respuestas concisas (máximo 3-4 párrafos) a menos que se pida detalle
- Puedes usar emojis relacionados con nutrición y salud para hacer la respuesta más visual
- Si detectas situaciones críticas (muchas alertas, alto riesgo), sugiere acciones concretas";

// ── Preparar mensajes para la API ────────────────────────────────
$messages = [];

// Agregar historial previo (últimos 10 mensajes para no exceder tokens)
$historial_reciente = array_slice($historial, -10);
foreach ($historial_reciente as $msg) {
    if (isset($msg['role']) && isset($msg['content'])) {
        $messages[] = [
            'role'    => $msg['role'],
            'content' => $msg['content']
        ];
    }
}

// Agregar el mensaje actual
$messages[] = [
    'role'    => 'user',
    'content' => $mensaje
];

// ── Llamada a Claude API ─────────────────────────────────────────
$api_payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 600,
    'system'     => $system_prompt,
    'messages'   => $messages
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $api_payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'anthropic-version: 2023-06-01',
        'x-api-key: ' . ($_ENV['ANTHROPIC_API_KEY'] ?? getenv('ANTHROPIC_API_KEY') ?? '')
    ],
    CURLOPT_TIMEOUT        => 30
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    // Respuesta de fallback si la API no está disponible
    $respuestas_fallback = [
        "hola"      => "¡Hola! Soy NutriBot 🥗, tu asistente nutricional. Actualmente hay $alertas_activas alertas activas y $en_riesgo estudiantes en riesgo alto. ¿En qué te puedo ayudar?",
        "alertas"   => "📊 Actualmente hay **$alertas_activas alertas activas** en el sistema. Te recomiendo revisar el módulo de Alertas para atender los casos más urgentes.",
        "riesgo"    => "🔴 Hay **$en_riesgo estudiantes en riesgo nutricional alto**. Puedes ver el detalle completo en el módulo de Análisis Predictivo.",
        "default"   => "Soy NutriBot 🤖. El sistema tiene $total_estudiantes estudiantes registrados, $alertas_activas alertas activas y $en_riesgo en riesgo alto. ¿Sobre qué módulo necesitas ayuda?"
    ];

    $respuesta_fb = $respuestas_fallback['default'];
    $msg_lower = strtolower($mensaje);
    foreach ($respuestas_fallback as $clave => $resp) {
        if (strpos($msg_lower, $clave) !== false) {
            $respuesta_fb = $resp;
            break;
        }
    }

    echo json_encode(['respuesta' => $respuesta_fb, 'fuente' => 'fallback']);
    exit;
}

$data = json_decode($response, true);
$texto = $data['content'][0]['text'] ?? 'Lo siento, no pude procesar tu consulta. Por favor intenta de nuevo.';

echo json_encode([
    'respuesta' => $texto,
    'fuente'    => 'claude'
]);
