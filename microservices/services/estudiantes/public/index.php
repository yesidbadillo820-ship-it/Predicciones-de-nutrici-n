<?php
// Estudiantes Service — Controller (Endpoint) → Validator → Repositorio.
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

if ($path === '/health') json_out(['status' => 'ok', 'service' => 'estudiantes']);

$repo = new EstudianteRepository(db());

// Validador de entrada
function validar_estudiante(array $in): array {
    $errores = [];
    foreach (['nombre', 'apellido', 'fecha_nac'] as $f) {
        if (trim((string) ($in[$f] ?? '')) === '') $errores[] = "$f es obligatorio";
    }
    if ((int) ($in['id_grado'] ?? 0) <= 0) $errores[] = 'id_grado es obligatorio';
    return $errores;
}
function normalizar(array $in): array {
    return [
        'nombre' => trim((string) ($in['nombre'] ?? '')),
        'apellido' => trim((string) ($in['apellido'] ?? '')),
        'fecha_nac' => (string) ($in['fecha_nac'] ?? ''),
        'genero' => ($in['genero'] ?? 'M') === 'F' ? 'F' : 'M',
        'id_grado' => (int) ($in['id_grado'] ?? 0),
        'peso_kg' => $in['peso_kg'] !== '' && isset($in['peso_kg']) ? (float) $in['peso_kg'] : null,
        'talla_cm' => $in['talla_cm'] !== '' && isset($in['talla_cm']) ? (float) $in['talla_cm'] : null,
        'nivel_riesgo' => $in['nivel_riesgo'] ?? 'sin_riesgo',
    ];
}

if ($path === '/grados' && $method === 'GET') json_out(['data' => $repo->grados()]);

if ($path === '/estudiantes') {
    if ($method === 'GET') {
        json_out(['data' => $repo->listar(trim((string) ($_GET['buscar'] ?? '')), trim((string) ($_GET['riesgo'] ?? '')))]);
    }
    if ($method === 'POST') {
        $in = body_json();
        if ($e = validar_estudiante($in)) json_out(['error' => 'Validación', 'detalles' => $e], 422);
        $id = $repo->crear(normalizar($in));
        json_out(['ok' => true, 'id' => $id, 'estudiante' => $repo->porId($id)], 201);
    }
}

if (preg_match('#^/estudiantes/(\d+)$#', $path, $m)) {
    $id = (int) $m[1];
    if ($method === 'GET') {
        $e = $repo->porId($id);
        $e ? json_out(['data' => $e]) : json_out(['error' => 'No encontrado'], 404);
    }
    if ($method === 'PUT') {
        $in = body_json();
        if ($err = validar_estudiante($in)) json_out(['error' => 'Validación', 'detalles' => $err], 422);
        $repo->actualizar($id, normalizar($in));
        json_out(['ok' => true, 'estudiante' => $repo->porId($id)]);
    }
    if ($method === 'DELETE') {
        $repo->eliminar($id);
        json_out(['ok' => true]);
    }
}

json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
