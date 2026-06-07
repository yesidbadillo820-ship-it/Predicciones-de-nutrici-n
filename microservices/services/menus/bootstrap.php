<?php
// bootstrap.php — utilidades comunes del microservicio (autocontenido).
declare(strict_types=1);

function env_(string $k, $d = null) { $v = getenv($k); return $v === false ? $d : $v; }

function db(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $c = new mysqli(
        (string) env_('DB_HOST', 'localhost'),
        (string) env_('DB_USER', 'root'),
        (string) env_('DB_PASS', ''),
        (string) env_('DB_NAME', 'nutripredict_db'),
        (int) env_('DB_PORT', 3306)
    );
    $c->set_charset('utf8mb4');
    return $c;
}

function body_json(): array {
    $d = json_decode((string) file_get_contents('php://input'), true);
    return is_array($d) ? $d : [];
}

function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── JWT mínimo (HS256), sin dependencias ──
function jwt_secret(): string { return (string) env_('JWT_SECRET', 'nutripredict-dev-secret'); }
function b64u(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

function jwt_encode(array $payload): string {
    $h = b64u((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['iat'] = time();
    $payload['exp'] = time() + 28800; // 8 h
    $p = b64u((string) json_encode($payload));
    $s = b64u(hash_hmac('sha256', "$h.$p", jwt_secret(), true));
    return "$h.$p.$s";
}

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

// ── Comunicación entre microservicios (HTTP/REST) ──
function service_url(string $name): string {
    return (string) env_(strtoupper($name) . '_URL', 'http://' . $name);
}
function svc_get(string $service, string $path): ?array {
    $ch = curl_init(service_url($service) . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json']]);
    $r = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($r === false || $code >= 400) return null;
    $d = json_decode((string) $r, true);
    return is_array($d) ? $d : null;
}
function svc_post(string $service, string $path, array $body): ?array {
    $ch = curl_init(service_url($service) . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json']]);
    $r = curl_exec($ch);
    curl_close($ch);
    $d = json_decode((string) $r, true);
    return is_array($d) ? $d : null;
}
