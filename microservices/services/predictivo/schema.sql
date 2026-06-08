-- Base de datos PROPIA del microservicio Predictivo.
CREATE DATABASE IF NOT EXISTS db_predictivo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_predictivo;

SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS riesgo_diario (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    fecha         DATE NOT NULL,
    nivel_riesgo  ENUM('sin_riesgo','bajo','medio','alto') NOT NULL DEFAULT 'sin_riesgo',
    score         INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_riesgo (id_estudiante, fecha),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
