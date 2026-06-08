-- Base de datos PROPIA del microservicio Auth (aislamiento de datos).
CREATE DATABASE IF NOT EXISTS db_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_auth;

SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS usuarios (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nombre   VARCHAR(120) NOT NULL,
    email    VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol      ENUM('admin','encargado_restaurante','docente','directora') NOT NULL DEFAULT 'docente',
    activo   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuarios demo (contraseña: demo123)
INSERT IGNORE INTO usuarios (nombre, email, password, rol) VALUES
('Administrador General', 'admin@nutripredict.edu.co',       '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'admin'),
('Encargado Restaurante', 'restaurante@nutripredict.edu.co', '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'encargado_restaurante'),
('Docente Demo',          'docente@nutripredict.edu.co',     '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'docente'),
('Directora Demo',        'directora@nutripredict.edu.co',   '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'directora');
