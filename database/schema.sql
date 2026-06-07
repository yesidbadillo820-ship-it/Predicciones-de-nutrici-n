-- =============================================================
-- NutriPredict Escolar — Esquema de base de datos
-- Motor: MySQL 8 / MariaDB 10.4+  ·  Charset: utf8mb4
-- =============================================================
-- Uso:
--   mysql -u root -p < database/schema.sql
--   mysql -u root -p nutripredict_db < database/seed.sql   (datos demo)
-- =============================================================

CREATE DATABASE IF NOT EXISTS nutripredict_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nutripredict_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ── Usuarios y roles ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(120) NOT NULL,
    email           VARCHAR(160) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    rol             ENUM('admin','encargado_restaurante','docente','directora') NOT NULL DEFAULT 'docente',
    activo          TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Grados / cursos ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS grados (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(60) NOT NULL,
    nivel   INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Estudiantes ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS estudiantes (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    nombre             VARCHAR(80)  NOT NULL,
    apellido           VARCHAR(80)  NOT NULL,
    fecha_nac          DATE         NOT NULL,
    genero             ENUM('M','F') NOT NULL DEFAULT 'M',
    id_grado           INT          NOT NULL,
    peso_kg            DECIMAL(5,2) NULL,
    talla_cm           DECIMAL(5,2) NULL,
    imc                DECIMAL(5,2) NULL,
    imc_clasificacion  VARCHAR(30)  NULL,
    nivel_riesgo       ENUM('sin_riesgo','bajo','medio','alto') NOT NULL DEFAULT 'sin_riesgo',
    score              INT          NOT NULL DEFAULT 0,
    activo             TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_creacion     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudiante_grado FOREIGN KEY (id_grado) REFERENCES grados(id),
    INDEX idx_estudiante_riesgo (nivel_riesgo),
    INDEX idx_estudiante_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Catálogo de alimentos (valores por 100 g) ────────────────
CREATE TABLE IF NOT EXISTS alimentos (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(120) NOT NULL,
    categoria        VARCHAR(60)  NOT NULL DEFAULT 'General',
    calorias         DECIMAL(8,2) NOT NULL DEFAULT 0,
    proteinas_g      DECIMAL(8,2) NOT NULL DEFAULT 0,
    carbohidratos_g  DECIMAL(8,2) NOT NULL DEFAULT 0,
    grasas_g         DECIMAL(8,2) NOT NULL DEFAULT 0,
    hierro_mg        DECIMAL(8,2) NOT NULL DEFAULT 0,
    calcio_mg        DECIMAL(8,2) NOT NULL DEFAULT 0,
    vitamina_d_ug    DECIMAL(8,2) NOT NULL DEFAULT 0,
    zinc_mg          DECIMAL(8,2) NOT NULL DEFAULT 0,
    activo           TINYINT(1)   NOT NULL DEFAULT 1,
    INDEX idx_alimento_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Menús diarios ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS menus (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    fecha            DATE NOT NULL,
    tipo_tiempo      ENUM('desayuno','almuerzo','merienda') NOT NULL DEFAULT 'almuerzo',
    descripcion      TEXT NOT NULL,
    nutrientes_cubre LONGTEXT NULL,   -- JSON con cobertura por nutriente
    totales_json     LONGTEXT NULL,   -- JSON con totales por nutriente
    id_usuario       INT NOT NULL,
    fecha_creacion   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_menu_fecha_tipo (fecha, tipo_tiempo),
    CONSTRAINT fk_menu_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Alimentos que componen cada menú ─────────────────────────
CREATE TABLE IF NOT EXISTS menu_alimentos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_menu     INT NOT NULL,
    id_alimento INT NOT NULL,
    porcion_g   DECIMAL(8,2) NOT NULL DEFAULT 100,
    UNIQUE KEY uq_menu_alimento (id_menu, id_alimento),
    CONSTRAINT fk_ma_menu     FOREIGN KEY (id_menu)     REFERENCES menus(id)     ON DELETE CASCADE,
    CONSTRAINT fk_ma_alimento FOREIGN KEY (id_alimento) REFERENCES alimentos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Asistencia al comedor ────────────────────────────────────
CREATE TABLE IF NOT EXISTS asistencia (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    fecha         DATE NOT NULL,
    asistio       TINYINT(1) NOT NULL DEFAULT 1,
    observacion   VARCHAR(255) NULL,
    UNIQUE KEY uq_asistencia (id_estudiante, fecha),
    CONSTRAINT fk_asistencia_est FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id) ON DELETE CASCADE,
    INDEX idx_asistencia_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Alertas nutricionales (id_estudiante = 0 → alerta de sistema) ──
CREATE TABLE IF NOT EXISTS alertas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante     INT NOT NULL DEFAULT 0,
    tipo_deficiencia  VARCHAR(60) NOT NULL,
    descripcion       TEXT NOT NULL,
    nivel             ENUM('baja','media','alta') NOT NULL DEFAULT 'media',
    estado            ENUM('activa','resuelta','ignorada') NOT NULL DEFAULT 'activa',
    fecha_creacion    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion  DATETIME NULL,
    INDEX idx_alerta_estado (estado),
    INDEX idx_alerta_estudiante (id_estudiante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Cobertura nutricional histórica (para reportes y predicción) ──
CREATE TABLE IF NOT EXISTS cobertura_nutricional (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    fecha       DATE NOT NULL,
    nutriente   VARCHAR(40) NOT NULL,
    porcentaje  DECIMAL(6,2) NOT NULL DEFAULT 0,
    INDEX idx_cobertura_fecha (fecha),
    INDEX idx_cobertura_nutriente (nutriente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Histórico diario de riesgo por estudiante ────────────────
CREATE TABLE IF NOT EXISTS riesgo_diario (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    fecha         DATE NOT NULL,
    nivel_riesgo  ENUM('sin_riesgo','bajo','medio','alto') NOT NULL DEFAULT 'sin_riesgo',
    score         INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_riesgo (id_estudiante, fecha),
    CONSTRAINT fk_riesgo_est FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id) ON DELETE CASCADE,
    INDEX idx_riesgo_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
