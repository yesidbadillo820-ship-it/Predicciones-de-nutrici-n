-- Base de datos PROPIA del microservicio Alertas.
CREATE DATABASE IF NOT EXISTS db_alertas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_alertas;

CREATE TABLE IF NOT EXISTS alertas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante    INT NOT NULL DEFAULT 0,
    tipo_deficiencia VARCHAR(60) NOT NULL,
    descripcion      TEXT NOT NULL,
    nivel            ENUM('baja','media','alta') NOT NULL DEFAULT 'media',
    estado           ENUM('activa','resuelta','ignorada') NOT NULL DEFAULT 'activa',
    fecha_creacion   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion DATETIME NULL,
    INDEX idx_estado (estado),
    INDEX idx_estudiante (id_estudiante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO alertas (id,id_estudiante,tipo_deficiencia,descripcion,nivel,estado) VALUES
(1,5,'Hierro','Deficiencia de hierro detectada en seguimiento.','alta','activa'),
(2,5,'Proteinas','Bajo aporte proteico en los últimos menús.','media','activa'),
(3,2,'Calcio','Cobertura de calcio por debajo del mínimo.','media','activa'),
(4,8,'Vitamina D','Niveles de vitamina D a vigilar.','baja','activa'),
(5,0,'calorias','El menú del día cubre solo el 62% de las calorías ICBF.','media','activa');
