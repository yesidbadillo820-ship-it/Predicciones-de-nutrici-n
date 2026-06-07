<?php
// models/UsuarioModel.php
class UsuarioModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email=? AND activo=1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerTodos() {
        return $this->conn->query("SELECT * FROM usuarios ORDER BY rol, nombre");
    }

    public function crear($nombre, $email, $password, $rol) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios(nombre,email,password,rol) VALUES(?,?,?,?)");
        $stmt->bind_param('ssss', $nombre, $email, $hash, $rol);
        return $stmt->execute();
    }

    public function actualizarRol($id, $rol) {
        $id = (int)$id;
        $rol = mysqli_real_escape_string($this->conn, $rol);
        return $this->conn->query("UPDATE usuarios SET rol='$rol' WHERE id=$id");
    }

    public function actualizarPassword($id, $nueva) {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE usuarios SET password=? WHERE id=?");
        $stmt->bind_param('si', $hash, $id);
        return $stmt->execute();
    }

    public function toggleActivo($id) {
        $id = (int)$id;
        $act = $this->conn->query("SELECT activo FROM usuarios WHERE id=$id")->fetch_assoc()['activo'];
        $nuevo = $act ? 0 : 1;
        $this->conn->query("UPDATE usuarios SET activo=$nuevo WHERE id=$id");
        return $nuevo;
    }

    public function contarPorRol($rol) {
        $rol = mysqli_real_escape_string($this->conn, $rol);
        return $this->conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE rol='$rol' AND activo=1")->fetch_assoc()['t'];
    }
}
