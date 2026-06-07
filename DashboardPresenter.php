<?php
// presenters/DashboardPresenter.php
require_once 'models/EstudianteModel.php';
require_once 'models/AlertaModel.php';
require_once 'models/MenuModel.php';
require_once 'models/AsistenciaModel.php';
require_once 'models/ReporteModel.php';

class DashboardPresenter {
    private $estudianteModel;
    private $alertaModel;
    private $menuModel;
    private $asistenciaModel;
    private $reporteModel;

    public function __construct($conn) {
        $this->estudianteModel  = new EstudianteModel($conn);
        $this->alertaModel      = new AlertaModel($conn);
        $this->menuModel        = new MenuModel($conn);
        $this->asistenciaModel  = new AsistenciaModel($conn);
        $this->reporteModel     = new ReporteModel($conn);
    }

    public function obtenerResumen() {
        return [
            'total_estudiantes' => $this->estudianteModel->contarTotal(),
            'total_alertas'     => $this->alertaModel->contarPorEstado('activa'),
            'en_riesgo'         => $this->estudianteModel->contarEnRiesgo(),
            'asistencia_hoy'    => $this->asistenciaModel->contarHoy(),
        ];
    }

    public function obtenerAlertasRecientes() {
        return $this->alertaModel->obtenerActivas(5);
    }

    public function obtenerEstudiantesEnRiesgo() {
        return $this->estudianteModel->obtenerTodos();
    }

    public function obtenerMenuHoy() {
        return $this->menuModel->obtenerMenuHoy();
    }

    public function obtenerCobertura() {
        return $this->reporteModel->coberturaSemanal();
    }

    public function obtenerTendencia() {
        $q = $this->reporteModel->tendenciaSemanal();
        $dias=[]; $altos=[]; $medios=[]; $bajos=[];
        if ($q && $q->num_rows > 0) {
            while ($r = $q->fetch_assoc()) {
                $dias[]   = $r['dia'];
                $altos[]  = (int)$r['alto'];
                $medios[] = (int)$r['medio'];
                $bajos[]  = (int)$r['bajo'];
            }
        } else {
            $dias=['Lun','Mar','Mié','Jue','Vie'];
            $altos=[3,4,2,5,3]; $medios=[6,7,5,8,6]; $bajos=[12,10,14,9,13];
        }
        return compact('dias','altos','medios','bajos');
    }

    public static function tiempoRelativo($datetime) {
        $diff = time() - strtotime($datetime);
        if ($diff < 3600)  return 'Hace ' . floor($diff/60) . ' min';
        if ($diff < 86400) return 'Hace ' . floor($diff/3600) . ' h';
        return 'Hace ' . floor($diff/86400) . ' día(s)';
    }
}
