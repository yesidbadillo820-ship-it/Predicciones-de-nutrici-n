<?php
// presenters/AuthPresenter.php
require_once 'models/UsuarioModel.php';

class AuthPresenter {
    private $model;
    public $error = '';

    // Límite de intentos fallidos para mitigar fuerza bruta
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_SEG  = 60;

    public function __construct($conn) {
        $this->model = new UsuarioModel($conn);
    }

    public function manejarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;

        // ── Rate limiting por sesión ──
        $intentos = $_SESSION['login_intentos'] ?? 0;
        $ultimo   = $_SESSION['login_ultimo'] ?? 0;
        if ($intentos >= self::MAX_INTENTOS && (time() - $ultimo) < self::BLOQUEO_SEG) {
            $espera = self::BLOQUEO_SEG - (time() - $ultimo);
            app_log('warning', 'Login bloqueado por rate limiting', ['intentos' => $intentos]);
            $this->error = "Demasiados intentos fallidos. Espera {$espera} segundos e inténtalo de nuevo.";
            return false;
        }
        if ((time() - $ultimo) >= self::BLOQUEO_SEG) {
            $intentos = 0; // se reinicia la ventana de bloqueo
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $usuario = $this->model->obtenerPorEmail($email);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Anti-fijación de sesión: ID nuevo al autenticar
            session_regenerate_id(true);
            unset($_SESSION['login_intentos'], $_SESSION['login_ultimo']);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario']    = [
                'id'     => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email'  => $usuario['email'],
                'rol'    => $usuario['rol'],
            ];
            app_log('info', 'Inicio de sesión exitoso', ['id' => $usuario['id'], 'rol' => $usuario['rol']]);
            return true;
        }

        $_SESSION['login_intentos'] = $intentos + 1;
        $_SESSION['login_ultimo']   = time();
        app_log('warning', 'Inicio de sesión fallido', ['email' => $email]);
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
