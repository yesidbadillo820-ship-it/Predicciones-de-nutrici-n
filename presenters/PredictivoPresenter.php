<?php
// presenters/PredictivoPresenter.php
require_once 'models/EstudianteModel.php';
require_once 'models/AlertaModel.php';
require_once 'models/ReporteModel.php';

class PredictivoPresenter {
    private $conn;
    private $estudianteModel;
    private $alertaModel;
    private $reporteModel;

    public function __construct($conn) {
        $this->conn             = $conn;
        $this->estudianteModel  = new EstudianteModel($conn);
        $this->alertaModel      = new AlertaModel($conn);
        $this->reporteModel     = new ReporteModel($conn);
    }

    // Motor de predicción: calcula score por estudiante
    public function calcularRiesgo($id_est) {
        $score = 0;
        $factores = [];

        // Factor 1: Alertas activas
        $al = $this->alertaModel->contarPorEstadoEstudiante($id_est, 'activa');
        if ($al >= 3)     { $score += 40; $factores[] = ['desc'=>"$al alertas activas", 'peso'=>40, 'tipo'=>'alto']; }
        elseif ($al >= 1) { $score += 25; $factores[] = ['desc'=>"$al alerta(s) activa(s)", 'peso'=>25, 'tipo'=>'medio']; }

        // Factor 2: Inasistencias últimos 10 días
        $aus = $this->conn->query("
            SELECT COUNT(*) AS t FROM asistencia
            WHERE id_estudiante=$id_est AND asistio=0
            AND fecha >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
        ")->fetch_assoc()['t'];
        if ($aus >= 5)     { $score += 20; $factores[] = ['desc'=>"$aus inasistencias en 10 días",'peso'=>20,'tipo'=>'alto']; }
        elseif ($aus >= 2) { $score += 10; $factores[] = ['desc'=>"$aus inasistencias recientes",'peso'=>10,'tipo'=>'medio']; }

        // Factor 3: Cobertura nutricional
        $cob = $this->conn->query("
            SELECT AVG(porcentaje) AS p FROM cobertura_nutricional
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ")->fetch_assoc()['p'] ?? 75;
        $cob = (float)$cob;
        if ($cob < 60)     { $score += 30; $factores[] = ['desc'=>"Cobertura muy baja (".round($cob)."%)",'peso'=>30,'tipo'=>'alto']; }
        elseif ($cob < 75) { $score += 15; $factores[] = ['desc'=>"Cobertura media (".round($cob)."%)",'peso'=>15,'tipo'=>'medio']; }
        else                { $factores[] = ['desc'=>"Cobertura adecuada (".round($cob)."%)",'peso'=>0,'tipo'=>'bajo']; }

        // Factor protector: alertas resueltas
        $res = $this->conn->query("SELECT COUNT(*) AS t FROM alertas WHERE id_estudiante=$id_est AND estado='resuelta'")->fetch_assoc()['t'];
        if ($res >= 2) { $score -= 10; $factores[] = ['desc'=>"$res alertas resueltas (factor positivo)",'peso'=>-10,'tipo'=>'bajo']; }

        $score = max(0, min(100, $score));
        $nivel = $score >= 70 ? 'alto' : ($score >= 40 ? 'medio' : ($score >= 15 ? 'bajo' : 'sin_riesgo'));

        return ['score'=>$score, 'nivel'=>$nivel, 'factores'=>$factores];
    }

    // Ejecutar predicción para todos los estudiantes
    public function ejecutarPrediccion() {
        $hoy = date('Y-m-d');
        $ests = $this->conn->query("SELECT id FROM estudiantes WHERE activo=1");
        while ($e = $ests->fetch_assoc()) {
            $r   = $this->calcularRiesgo($e['id']);
            $id  = $e['id'];
            $niv = $r['nivel'];
            $sc  = $r['score'];
            $this->conn->query("
                INSERT INTO riesgo_diario(id_estudiante,fecha,nivel_riesgo,score)
                VALUES($id,'$hoy','$niv',$sc)
                ON DUPLICATE KEY UPDATE nivel_riesgo='$niv',score=$sc
            ");
            $this->estudianteModel->actualizarRiesgo($id, $niv, $sc);
            if ($niv === 'alto' && !$this->alertaModel->existeActivaPorEstudiante($id, 'Riesgo General')) {
                $desc = "Score predictivo: $sc/100. Riesgo nutricional alto detectado automáticamente.";
                $this->alertaModel->crear($id, 'Riesgo General', $desc, 'alta');
            }
        }
    }

    public function obtenerEstudiantesConScore() {
        $hoy = date('Y-m-d');
        return $this->conn->query("
            SELECT e.id, e.nombre, e.apellido, g.nombre AS grado, e.nivel_riesgo,
                   rd.score,
                   (SELECT COUNT(*) FROM alertas WHERE id_estudiante=e.id AND estado='activa') AS alertas_act,
                   (SELECT COUNT(*) FROM asistencia WHERE id_estudiante=e.id AND asistio=0
                    AND fecha >= DATE_SUB('$hoy', INTERVAL 10 DAY)) AS inasistencias
            FROM estudiantes e
            JOIN grados g ON e.id_grado=g.id
            LEFT JOIN riesgo_diario rd ON rd.id_estudiante=e.id AND rd.fecha='$hoy'
            WHERE e.activo=1
            ORDER BY FIELD(e.nivel_riesgo,'alto','medio','bajo','sin_riesgo'), rd.score DESC
        ");
    }

    public function obtenerConteos() {
        $c = [];
        foreach (['alto','medio','bajo','sin_riesgo'] as $n) {
            $c[$n] = $this->conn->query("SELECT COUNT(*) AS t FROM estudiantes WHERE nivel_riesgo='$n' AND activo=1")->fetch_assoc()['t'];
        }
        return $c;
    }

    public function obtenerTendencia() {
        return $this->reporteModel->tendenciaSemanal();
    }

    public function obtenerDetalleEstudiante($id) {
        $est = $this->estudianteModel->obtenerPorId($id);
        if (!$est) return null;
        $r = $this->calcularRiesgo($id);
        $est['score_calc'] = $r['score'];
        $est['factores']   = $r['factores'];
        $est['historial']  = $this->conn->query("SELECT fecha,nivel_riesgo,score FROM riesgo_diario WHERE id_estudiante=$id ORDER BY fecha DESC LIMIT 14");
        $est['alertas']    = $this->conn->query("SELECT * FROM alertas WHERE id_estudiante=$id ORDER BY fecha_creacion DESC LIMIT 5");
        return $est;
    }
}
