<?php
// Repository.php — Capa de acceso a datos del microservicio Auth.
declare(strict_types=1);

class UsuarioRepository {
    public function __construct(private mysqli $db) {}

    public function porEmail(string $email): ?array {
        $st = $this->db->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1");
        $st->bind_param('s', $email);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    public function listar(): array {
        $r = $this->db->query("SELECT id, nombre, email, rol, activo FROM usuarios ORDER BY rol, nombre");
        return $r->fetch_all(MYSQLI_ASSOC);
    }
}
