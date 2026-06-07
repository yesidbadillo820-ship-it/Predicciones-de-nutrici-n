<?php
/**
 * bin/run_prediction.php — Recálculo diario del riesgo predictivo (para cron).
 *
 * Ejemplo de cron (cada día a las 5:00 a. m.):
 *   0 5 * * *  php /ruta/al/proyecto/bin/run_prediction.php >> /var/log/nutripredict_pred.log 2>&1
 */

// Las inclusiones del proyecto son relativas a la raíz: fijamos el CWD.
chdir(dirname(__DIR__));

require 'includes/db.php';                       // $conn
require 'presenters/PredictivoPresenter.php';

$inicio = microtime(true);
$presenter = new PredictivoPresenter($conn);
$presenter->ejecutarPrediccion();
$ms = round((microtime(true) - $inicio) * 1000);

echo date('c') . " — Predicción ejecutada para todos los estudiantes activos ({$ms} ms).\n";
