<?php
// tests/phpstan-bootstrap.php — Solo para análisis estático.
// config/config.php define estas constantes en tiempo de ejecución mediante
// define() con valores dinámicos (env), que PHPStan no puede seguir. Las
// declaramos aquí para que el análisis las reconozca.
defined('APP_ENV')          || define('APP_ENV', 'test');
defined('APP_DEBUG')        || define('APP_DEBUG', false);
defined('DB_HOST')          || define('DB_HOST', '');
defined('DB_USER')          || define('DB_USER', '');
defined('DB_PASS')          || define('DB_PASS', '');
defined('DB_NAME')          || define('DB_NAME', '');
defined('DB_PORT')          || define('DB_PORT', 3306);
defined('ANTHROPIC_API_KEY')|| define('ANTHROPIC_API_KEY', '');
defined('ANTHROPIC_MODEL')  || define('ANTHROPIC_MODEL', '');
