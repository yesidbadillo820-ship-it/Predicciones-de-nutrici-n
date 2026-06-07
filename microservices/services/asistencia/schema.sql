-- Base de datos PROPIA del microservicio Asistencia.
CREATE DATABASE IF NOT EXISTS db_asistencia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_asistencia;

CREATE TABLE IF NOT EXISTS asistencia (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    fecha         DATE NOT NULL,
    asistio       TINYINT(1) NOT NULL DEFAULT 1,
    observacion   VARCHAR(255) NULL,
    UNIQUE KEY uq_asistencia (id_estudiante, fecha),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO asistencia (id_estudiante,fecha,asistio,observacion) VALUES
(1,CURDATE(),1,''),(2,CURDATE(),0,'Cita médica'),(3,CURDATE(),1,''),(4,CURDATE(),1,''),
(5,CURDATE(),0,''),(6,CURDATE(),1,''),(7,CURDATE(),1,''),(8,CURDATE(),1,''),
(5,DATE_SUB(CURDATE(),INTERVAL 1 DAY),0,''),(2,DATE_SUB(CURDATE(),INTERVAL 1 DAY),0,''),
(5,DATE_SUB(CURDATE(),INTERVAL 2 DAY),0,''),(2,DATE_SUB(CURDATE(),INTERVAL 2 DAY),0,'');
