<?php
// presenters/AsistenciaPresenter.php
require_once 'models/AsistenciaModel.php';
require_once 'models/EstudianteModel.php';

class AsistenciaPresenter {
    private $model;
    private $estudianteModel;
    public $msg = '';

    public function __construct($conn) {
        $this->model           = new AsistenciaModel($conn);
        $this->estudianteModel = new EstudianteModel($conn);
    }

    public function manejarRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $asistencias  = $_POST['asistencia'] ?? [];
        $observaciones = $_POST['obs'] ?? [];

        // Obtener todos los IDs activos
        $q = $this->estudianteModel->obtenerTodos();
        $ids = [];
        while ($e = $q->fetch_assoc()) $ids[] = $e['id'];

        $this->model->guardar($fecha, $ids, $asistencias, $observaciones);
        $this->msg = 'Asistencia guardada correctamente.';
    }

    public function obtenerEstudiantesConAsistencia($fecha) {
        return $this->model->obtenerPorFecha($fecha);
    }

    public function obtenerResumen($fecha) {
        return $this->model->resumenFecha($fecha);
    }

    public function obtenerHistorial() {
        return $this->model->historial(14);
    }
}
