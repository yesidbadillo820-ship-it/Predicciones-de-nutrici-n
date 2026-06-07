<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'alertas']);

$repo = new AlertaRepository(db());

if ($path === '/alertas/conteos' && $method === 'GET') json_out(['data' => $repo->conteos()]);

// Endpoint usado por el microservicio Predictivo (comunicación interna)
if (preg_match('#^/alertas/estudiante/(\d+)/count$#', $path, $m) && $method === 'GET') {
    $estado = trim((string) ($_GET['estado'] ?? 'activa'));
    json_out(['count' => $repo->contarEstudiante((int) $m[1], $estado)]);
}

if ($path === '/alertas') {
    if ($method === 'GET') json_out(['data' => $repo->porEstado(trim((string) ($_GET['estado'] ?? 'activa')))]);
    if ($method === 'POST') {
        $in = body_json();
        $idEst = (int) ($in['id_estudiante'] ?? 0);
        $tipo = trim((string) ($in['tipo_deficiencia'] ?? ''));
        $desc = trim((string) ($in['descripcion'] ?? ''));
        if ($tipo === '' || $desc === '') json_out(['error' => 'tipo_deficiencia y descripcion son obligatorios'], 422);
        $nivel = in_array($in['nivel'] ?? 'media', ['baja','media','alta'], true) ? $in['nivel'] : 'media';
        json_out(['ok' => true, 'id' => $repo->crear($idEst, $tipo, $desc, $nivel)], 201);
    }
}
if (preg_match('#^/alertas/(\d+)/resolver$#', $path, $m) && $method === 'POST') { $repo->resolver((int) $m[1]); json_out(['ok' => true]); }
if (preg_match('#^/alertas/(\d+)/ignorar$#', $path, $m) && $method === 'POST') { $repo->ignorar((int) $m[1]); json_out(['ok' => true]); }

json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
