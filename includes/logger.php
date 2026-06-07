<?php
// includes/logger.php — Registro estructurado (JSON lines), sin dependencias.
// Escribe en logs/app.log. Pensado para enviarse luego a un colector central
// (ELK, Loki, CloudWatch…) o sustituirse por Monolog si se desea.

if (!function_exists('app_log')) {
    /**
     * @param string $level    debug|info|warning|error
     * @param string $message  mensaje legible
     * @param array<string,mixed> $context  datos adicionales
     */
    function app_log(string $level, string $message, array $context = []): void {
        $dir = dirname(__DIR__) . '/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $entry = [
            'ts'    => date('c'),
            'level' => strtoupper($level),
            'msg'   => $message,
        ];
        if (defined('APP_ENV')) {
            $entry['env'] = APP_ENV;
        }
        if ($context) {
            $entry['ctx'] = $context;
        }
        @file_put_contents(
            $dir . '/app.log',
            json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
