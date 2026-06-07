<?php
// Auth Service — Controller / Endpoint (capa externa del microservicio).
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

if ($path === '/health') {
    json_out(['status' => 'ok', 'service' => 'auth']);
}

$repo = new UsuarioRepository(db());

// POST /login — Validator → Lógica → emisión de JWT
if ($method === 'POST' && $path === '/login') {
    $in = body_json();
    $email = trim((string) ($in['email'] ?? ''));
    $pass  = (string) ($in['password'] ?? '');
    if ($email === '' || $pass === '') {
        json_out(['error' => 'email y password son obligatorios'], 422);
    }
    $u = $repo->porEmail($email);
    if (!$u || !password_verify($pass, $u['password'])) {
        json_out(['error' => 'Credenciales inválidas'], 401);
    }
    unset($u['password']);
    $token = jwt_encode(['sub' => (int) $u['id'], 'rol' => $u['rol'], 'nombre' => $u['nombre']]);
    json_out(['token' => $token, 'usuario' => $u]);
}

// GET /usuarios — listado (protegido por el gateway)
if ($method === 'GET' && $path === '/usuarios') {
    json_out(['data' => $repo->listar()]);
}

json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
