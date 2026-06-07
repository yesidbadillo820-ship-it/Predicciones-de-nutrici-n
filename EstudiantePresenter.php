<?php
// presenters/EstudiantePresenter.php
require_once 'models/EstudianteModel.php';

class EstudiantePresenter {
    private $model;
    public $msg = '';
    public $err = '';
    public $datos = [];

    public function __construct($conn) {
        $this->model = new EstudianteModel($conn);
    }

    public function manejarRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear') {
            $this->crear();
        } elseif ($accion === 'editar') {
            $this->editar();
        }

        if (isset($_GET['del'])) {
            $this->eliminar((int)$_GET['del']);
        }
    }

    private function crear() {
        $datos = $this->validar($_POST);
        if ($datos === false) return;

        $this->model->crear($datos)
            ? $this->msg = 'Estudiante registrado correctamente.'
            : $this->err = 'Error al guardar. Intenta de nuevo.';
    }

    private function editar() {
        $id = (int)($_POST['id'] ?? 0);
        $datos = $this->validar($_POST, true);
        if ($datos === false) return;

        $this->model->actualizar($id, $datos)
            ? $this->msg = 'Estudiante actualizado correctamente.'
            : $this->err = 'Error al actualizar.';
    }

    private function eliminar($id) {
        $this->model->eliminar($id);
        $this->msg = 'Estudiante eliminado.';
    }

    private function validar($post, $editar = false) {
        $datos = [
            'nombre'       => trim($post['nombre'] ?? ''),
            'apellido'     => trim($post['apellido'] ?? ''),
            'fecha_nac'    => $post['fecha_nac'] ?? '',
            'genero'       => $post['genero'] ?? 'M',
            'id_grado'     => (int)($post['id_grado'] ?? 0),
            'peso_kg'      => $post['peso_kg'] ?: null,
            'talla_cm'     => $post['talla_cm'] ?: null,
            'nivel_riesgo' => $post['nivel_riesgo'] ?? 'sin_riesgo',
        ];

        if (!$datos['nombre'] || !$datos['apellido'] || !$datos['fecha_nac'] || !$datos['id_grado']) {
            $this->err = 'Completa todos los campos obligatorios.';
            return false;
        }
        return $datos;
    }

    // Datos para la Vista
    public function obtenerEstudiantes($buscar = '') {
        return $this->model->obtenerTodos($buscar);
    }

    public function obtenerGrados() {
        return $this->model->obtenerGrados();
    }

    public function obtenerEstudiantePorId($id) {
        return $this->model->obtenerPorId($id);
    }
}
