<?php
// includes/csrf.php — Protección contra CSRF (Cross-Site Request Forgery)
// Requiere que la sesión ya esté iniciada (ver includes/auth.php).

/** Devuelve el token CSRF de la sesión, creándolo si no existe. */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Imprime el campo oculto con el token, para insertar dentro de <form>. */
function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/** Comprueba si la petición actual trae un token válido (POST o cabecera). */
function csrf_check(): bool {
    $enviado = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $esperado = $_SESSION['csrf_token'] ?? '';
    return $enviado !== '' && $esperado !== '' && hash_equals($esperado, $enviado);
}

/**
 * Exige un token válido en peticiones POST. Si falla, responde 403 y termina.
 * Se invoca automáticamente desde includes/auth.php para todas las páginas.
 */
function csrf_require(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    if (csrf_check()) {
        return;
    }
    http_response_code(403);
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Token CSRF inválido o ausente.']);
    } else {
        echo 'Solicitud rechazada: token de seguridad (CSRF) inválido o ausente. Recarga la página e inténtalo de nuevo.';
    }
    exit;
}
