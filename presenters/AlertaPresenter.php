<?php
// presenters/AlertaPresenter.php
require_once 'models/AlertaModel.php';
require_once 'models/EstudianteModel.php';

class AlertaPresenter {
    private $model;
    private $estudianteModel;
    public $msg = '';

    public function __construct($conn) {
        $this->model           = new AlertaModel($conn);
        $this->estudianteModel = new EstudianteModel($conn);
    }

    public function manejarRequest() {
        if (isset($_GET['resolver'])) {
            $this->model->resolver((int)$_GET['resolver']);
            $this->msg = 'Alerta marcada como resuelta.';
        }
        if (isset($_GET['ignorar'])) {
            $this->model->ignorar((int)$_GET['ignorar']);
            $this->msg = 'Alerta ignorada.';
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion']??'') === 'crear') {
            $this->crear();
        }
    }

    private function crear() {
        $id  = (int)($_POST['id_estudiante'] ?? 0);
        $tipo = trim($_POST['tipo_deficiencia'] ?? '');
        $desc = trim($_POST['descripcion'] ?? '');
        $nivel = $_POST['nivel'] ?? 'media';
        if ($id && $tipo && $desc) {
            $this->model->crear($id, $tipo, $desc, $nivel);
            $this->msg = 'Alerta creada correctamente.';
        }
    }

    public function obtenerAlertas($estado = 'activa') {
        return $this->model->obtenerPorEstado($estado);
    }

    public function obtenerConteos() {
        $conteos = [];
        foreach (['activa','resuelta','ignorada'] as $e) {
            $conteos[$e] = $this->model->contarPorEstado($e);
        }
        return $conteos;
    }

    public function obtenerEstudiantes() {
        return $this->estudianteModel->obtenerTodos();
    }

    public static function icono($tipo) {
        $iconos = ['Hierro'=>'🩸','Calcio'=>'🦴','Proteinas'=>'💪','Vitamina D'=>'☀️','Zinc'=>'🔬','Riesgo General'=>'⚠️'];
        return $iconos[$tipo] ?? '⚠️';
    }
}
