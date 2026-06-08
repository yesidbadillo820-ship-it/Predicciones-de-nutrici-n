<?php
// API Gateway — único punto de entrada. Valida JWT y enruta a los microservicios.
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

// Servicios registrados (el gateway los conoce internamente; afuera expone un solo endpoint)
const SERVICIOS = ['auth', 'estudiantes', 'menus', 'alimentos', 'asistencia', 'alertas', 'predictivo', 'reportes', 'nutribot'];

$method = $_SERVER['REQUEST_METHOD'];
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// CORS básico (para front desacoplado)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($method === 'OPTIONS') { http_response_code(204); exit; }

if ($path === '/health') {
    json_out(['status' => 'ok', 'service' => 'gateway', 'servicios' => SERVICIOS]);
}

// Rutas que no son de la API → servir la interfaz web (SPA) en el mismo origen
if (!str_starts_with($path, '/api/')) {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/app.html');
    exit;
}

if (!preg_match('#^/api/([a-z]+)(/.*)?$#', $path, $m)) {
    json_out(['error' => 'Ruta no encontrada. Usa /api/{servicio}/...'], 404);
}
$svc  = $m[1];
$rest = $m[2] ?? '/';
if (!in_array($svc, SERVICIOS, true)) {
    json_out(['error' => "Servicio '$svc' no existe", 'disponibles' => SERVICIOS], 404);
}

// Autenticación centralizada: rutas públicas vs protegidas
$esPublica = ($svc === 'auth' && $rest === '/login') || $rest === '/health';
$user = null;
if (!$esPublica) {
    $user = jwt_verify(bearer_token());
    if (!$user) {
        json_out(['error' => 'No autorizado: token JWT ausente o inválido'], 401);
    }
}

// Reenviar la petición al microservicio (proxy)
$url = service_url($svc) . $rest;
$headers = ['Accept: application/json', 'Content-Type: application/json'];
if ($user) {
    $headers[] = 'X-User-Id: ' . ($user['sub'] ?? '');
    $headers[] = 'X-User-Rol: ' . ($user['rol'] ?? '');
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_POSTFIELDS     => file_get_contents('php://input'),
    CURLOPT_TIMEOUT        => 15,
]);
$resp = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($resp === false) {
    json_out(['error' => 'Servicio no disponible', 'service' => $svc, 'detalle' => $err], 503);
}
http_response_code($code ?: 502);
header('Content-Type: application/json');
echo $resp;
