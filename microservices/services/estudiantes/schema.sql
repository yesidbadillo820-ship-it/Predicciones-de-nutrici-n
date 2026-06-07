-- Base de datos PROPIA del microservicio Estudiantes.
CREATE DATABASE IF NOT EXISTS db_estudiantes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_estudiantes;

CREATE TABLE IF NOT EXISTS grados (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    nivel  INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estudiantes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nombre            VARCHAR(80) NOT NULL,
    apellido          VARCHAR(80) NOT NULL,
    fecha_nac         DATE NOT NULL,
    genero            ENUM('M','F') NOT NULL DEFAULT 'M',
    id_grado          INT NOT NULL,
    peso_kg           DECIMAL(5,2) NULL,
    talla_cm          DECIMAL(5,2) NULL,
    imc               DECIMAL(5,2) NULL,
    imc_clasificacion VARCHAR(30) NULL,
    nivel_riesgo      ENUM('sin_riesgo','bajo','medio','alto') NOT NULL DEFAULT 'sin_riesgo',
    score             INT NOT NULL DEFAULT 0,
    activo            TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_est_grado FOREIGN KEY (id_grado) REFERENCES grados(id),
    INDEX idx_riesgo (nivel_riesgo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO grados (id, nombre, nivel) VALUES
(1,'Primero A',1),(2,'Primero B',1),(3,'Segundo A',2),(4,'Segundo B',2),
(5,'Tercero A',3),(6,'Cuarto A',4),(7,'Quinto A',5);

INSERT IGNORE INTO estudiantes (id,nombre,apellido,fecha_nac,genero,id_grado,peso_kg,talla_cm,imc,imc_clasificacion,nivel_riesgo,score) VALUES
(1,'Sofía','Ramírez','2017-03-12','F',1,20.5,115.0,15.50,'Normal','sin_riesgo',5),
(2,'Mateo','González','2017-07-22','M',1,18.0,112.0,14.35,'Bajo peso','medio',45),
(3,'Valentina','López','2016-01-05','F',3,22.0,120.0,15.28,'Normal','bajo',20),
(4,'Samuel','Martínez','2015-11-18','M',5,30.0,132.0,17.22,'Normal','sin_riesgo',8),
(5,'Isabella','Torres','2016-09-30','F',3,19.0,118.0,13.65,'Bajo peso','alto',72),
(6,'Santiago','Rojas','2017-05-14','M',2,21.0,116.0,15.61,'Normal','sin_riesgo',3),
(7,'Mariana','Castro','2015-02-27','F',6,28.0,130.0,16.57,'Normal','bajo',18),
(8,'Juan','Pérez','2016-12-08','M',4,24.0,124.0,15.61,'Normal','medio',50);
