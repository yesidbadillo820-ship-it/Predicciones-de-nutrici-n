-- Base de datos PROPIA del microservicio Menús.
CREATE DATABASE IF NOT EXISTS db_menus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_menus;

SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS menus (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    fecha            DATE NOT NULL,
    tipo_tiempo      ENUM('desayuno','almuerzo','merienda') NOT NULL DEFAULT 'almuerzo',
    descripcion      TEXT NOT NULL,
    nutrientes_cubre LONGTEXT NULL,
    fecha_creacion   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_menu (fecha, tipo_tiempo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_alimentos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_menu     INT NOT NULL,
    id_alimento INT NOT NULL,
    porcion_g   DECIMAL(8,2) NOT NULL DEFAULT 100,
    UNIQUE KEY uq_ma (id_menu, id_alimento),
    CONSTRAINT fk_ma_menu FOREIGN KEY (id_menu) REFERENCES menus(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cobertura_nutricional (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    fecha      DATE NOT NULL,
    nutriente  VARCHAR(40) NOT NULL,
    porcentaje DECIMAL(6,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_cobertura (fecha, nutriente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO cobertura_nutricional (fecha,nutriente,porcentaje) VALUES
(CURDATE(),'Hierro',68),(CURDATE(),'Calcio',74),(CURDATE(),'Proteinas',88),(CURDATE(),'Vitamina D',55),(CURDATE(),'Zinc',80);
