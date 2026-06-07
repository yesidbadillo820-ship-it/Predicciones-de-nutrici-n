<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'alimentos']);

$repo = new AlimentoRepository(db());

if ($path === '/alimentos') {
    if ($method === 'GET') json_out(['data' => $repo->listar(trim((string) ($_GET['categoria'] ?? '')))]);
    if ($method === 'POST') {
        $in = body_json();
        if (trim((string) ($in['nombre'] ?? '')) === '') json_out(['error' => 'nombre es obligatorio'], 422);
        $nums = ['calorias','proteinas_g','carbohidratos_g','grasas_g','hierro_mg','calcio_mg','vitamina_d_ug','zinc_mg'];
        $d = ['nombre' => trim((string) $in['nombre']), 'categoria' => trim((string) ($in['categoria'] ?? 'General'))];
        foreach ($nums as $n) $d[$n] = (float) ($in[$n] ?? 0);
        json_out(['ok' => true, 'id' => $repo->crear($d)], 201);
    }
}
if (preg_match('#^/alimentos/(\d+)$#', $path, $m) && $method === 'DELETE') {
    $repo->eliminar((int) $m[1]);
    json_out(['ok' => true]);
}
json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
