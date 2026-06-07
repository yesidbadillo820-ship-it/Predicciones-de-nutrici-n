<?php
// presenters/AuthPresenter.php
require_once 'models/UsuarioModel.php';

class AuthPresenter {
    private $model;
    public $error = '';

    public function __construct($conn) {
        $this->model = new UsuarioModel($conn);
    }

    public function manejarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $usuario = $this->model->obtenerPorEmail($email);

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario']    = [
                'id'     => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email'  => $usuario['email'],
                'rol'    => $usuario['rol'],
            ];
            return true;
        }

        $this->error = 'Correo o contraseña incorrectos.';
        return false;
    }
}

// ─────────────────────────────────────────────
// presenters/UsuarioPresenter.php
class UsuarioPresenter {
    private $model;
    public $msg = '';
    public $err = '';

    public function __construct($conn) {
        $this->model = new UsuarioModel($conn);
    }

    public function manejarRequest() {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $pass   = $_POST['password'] ?? '';
            $rol    = $_POST['rol'] ?? 'docente';
            if ($nombre && $email && $pass) {
                $this->model->crear($nombre, $email, $pass, $rol)
                    ? $this->msg = "Usuario '$nombre' creado."
                    : $this->err = 'Error: el correo puede ya existir.';
            } else {
                $this->err = 'Completa todos los campos.';
            }
        }

        if ($accion === 'rol') {
            $this->model->actualizarRol((int)$_POST['id'], $_POST['rol']);
            $this->msg = 'Rol actualizado.';
        }

        if ($accion === 'resetpass') {
            $pass = trim($_POST['nueva_pass'] ?? '');
            $pass
                ? ($this->model->actualizarPassword((int)$_POST['id'], $pass) && $this->msg = 'Contraseña actualizada.')
                : $this->err = 'Escribe una contraseña.';
        }

        if (isset($_GET['toggle'])) {
            $nuevo = $this->model->toggleActivo((int)$_GET['toggle']);
            $this->msg = $nuevo ? 'Usuario activado.' : 'Usuario desactivado.';
        }
    }

    public function obtenerUsuarios() {
        return $this->model->obtenerTodos();
    }

    public function contarPorRol($rol) {
        return $this->model->contarPorRol($rol);
    }
}
