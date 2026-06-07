<?php
// bootstrap.php — utilidades comunes del API Gateway.
declare(strict_types=1);

function env_(string $k, $d = null) { $v = getenv($k); return $v === false ? $d : $v; }

function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jwt_secret(): string { return (string) env_('JWT_SECRET', 'nutripredict-dev-secret'); }
function b64u(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

function jwt_verify(?string $token): ?array {
    if (!$token) return null;
    $x = explode('.', $token);
    if (count($x) !== 3) return null;
    [$h, $p, $s] = $x;
    $calc = b64u(hash_hmac('sha256', "$h.$p", jwt_secret(), true));
    if (!hash_equals($calc, $s)) return null;
    $payload = json_decode((string) base64_decode(strtr($p, '-_', '+/')), true);
    if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) return null;
    return $payload;
}

function bearer_token(): ?string {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (preg_match('/Bearer\s+(.+)/i', (string) $h, $m)) return trim($m[1]);
    return null;
}

/** URL base de un microservicio (configurable por entorno; por defecto, nombre de contenedor). */
function service_url(string $name): string {
    return (string) env_(strtoupper($name) . '_URL', 'http://' . $name);
}
