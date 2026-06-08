-- =============================================================
-- NutriPredict Escolar — Datos de demostración (seed)
-- Ejecutar DESPUÉS de schema.sql:
--   mysql -u root -p nutripredict_db < database/seed.sql
-- -------------------------------------------------------------
-- Credenciales demo (todas con contraseña: demo123)
--   admin@nutripredict.edu.co        → admin
--   restaurante@nutripredict.edu.co  → encargado_restaurante
--   docente@nutripredict.edu.co      → docente
--   directora@nutripredict.edu.co    → directora
-- El hash bcrypt corresponde a "demo123".
-- =============================================================

USE nutripredict_db;

SET NAMES utf8mb4;
-- ── Usuarios (password = demo123) ────────────────────────────
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador General', 'admin@nutripredict.edu.co',       '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'admin'),
('Encargado Restaurante', 'restaurante@nutripredict.edu.co', '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'encargado_restaurante'),
('Docente Demo',          'docente@nutripredict.edu.co',     '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'docente'),
('Directora Demo',        'directora@nutripredict.edu.co',   '$2y$12$HCdAlu0EV70D9OLLiixu8e.NEu040eXBeI5k0o.Kv85sv0.gu5cQ.', 'directora');

-- ── Grados ───────────────────────────────────────────────────
INSERT INTO grados (nombre, nivel) VALUES
('Primero A', 1), ('Primero B', 1),
('Segundo A', 2), ('Segundo B', 2),
('Tercero A', 3), ('Cuarto A', 4), ('Quinto A', 5);

-- ── Alimentos (valores aproximados por 100 g) ────────────────
INSERT INTO alimentos (nombre, categoria, calorias, proteinas_g, carbohidratos_g, grasas_g, hierro_mg, calcio_mg, vitamina_d_ug, zinc_mg) VALUES
('Arroz blanco cocido', 'Cereales',     130, 2.7, 28.0, 0.3, 0.2,  10, 0.0, 0.5),
('Frijol rojo cocido',  'Leguminosas',  127, 8.7, 22.8, 0.5, 2.9,  35, 0.0, 1.0),
('Lentejas cocidas',    'Leguminosas',  116, 9.0, 20.1, 0.4, 3.3,  19, 0.0, 1.3),
('Pollo pechuga',       'Proteínas',    165, 31.0, 0.0, 3.6, 0.7,  15, 0.1, 1.0),
('Huevo cocido',        'Proteínas',    155, 13.0, 1.1, 11.0, 1.2, 50, 2.0, 1.3),
('Leche entera',        'Lácteos',       61, 3.2, 4.8, 3.3, 0.0, 113, 1.3, 0.4),
('Yogur natural',       'Lácteos',       59, 10.0, 3.6, 0.4, 0.1, 110, 0.1, 0.6),
('Queso campesino',     'Lácteos',      264, 18.0, 1.3, 21.0, 0.7, 700, 0.6, 2.9),
('Espinaca cocida',     'Verduras',      23, 3.0, 3.8, 0.3, 3.6,  99, 0.0, 0.8),
('Plátano maduro',      'Frutas',        89, 1.1, 22.8, 0.3, 0.3,   5, 0.0, 0.2),
('Arepa de maíz',       'Cereales',     219, 5.0, 45.0, 2.5, 1.5,  90, 0.0, 0.7),
('Papa cocida',         'Tubérculos',    87, 1.9, 20.1, 0.1, 0.3,   5, 0.0, 0.3),
('Atún en agua',        'Proteínas',    116, 26.0, 0.0, 1.0, 1.0,  10, 1.7, 0.6),
('Sardina en lata',     'Proteínas',    208, 25.0, 0.0, 11.0, 2.9, 382, 4.8, 1.3),
('Zanahoria',           'Verduras',      41, 0.9, 9.6, 0.2, 0.3,  33, 0.0, 0.2);

-- ── Estudiantes ──────────────────────────────────────────────
INSERT INTO estudiantes (nombre, apellido, fecha_nac, genero, id_grado, peso_kg, talla_cm, imc, imc_clasificacion, nivel_riesgo, score) VALUES
('Sofía',   'Ramírez',  '2017-03-12', 'F', 1, 20.5, 115.0, 15.50, 'Normal',     'sin_riesgo', 5),
('Mateo',   'González', '2017-07-22', 'M', 1, 18.0, 112.0, 14.35, 'Bajo peso',  'medio',      45),
('Valentina','López',   '2016-01-05', 'F', 3, 22.0, 120.0, 15.28, 'Normal',     'bajo',       20),
('Samuel',  'Martínez', '2015-11-18', 'M', 5, 30.0, 132.0, 17.22, 'Normal',     'sin_riesgo', 8),
('Isabella','Torres',   '2016-09-30', 'F', 3, 19.0, 118.0, 13.65, 'Bajo peso',  'alto',       72),
('Santiago','Rojas',    '2017-05-14', 'M', 2, 21.0, 116.0, 15.61, 'Normal',     'sin_riesgo', 3),
('Mariana', 'Castro',   '2015-02-27', 'F', 6, 28.0, 130.0, 16.57, 'Normal',     'bajo',       18),
('Juan',    'Pérez',    '2016-12-08', 'M', 4, 24.0, 124.0, 15.61, 'Normal',     'medio',      50);

-- ── Asistencia (últimos días) ────────────────────────────────
INSERT INTO asistencia (id_estudiante, fecha, asistio, observacion) VALUES
(1, CURDATE(), 1, ''), (2, CURDATE(), 0, 'Cita médica'), (3, CURDATE(), 1, ''),
(4, CURDATE(), 1, ''), (5, CURDATE(), 0, ''), (6, CURDATE(), 1, ''),
(7, CURDATE(), 1, ''), (8, CURDATE(), 1, ''),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, ''), (2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 0, ''),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 0, ''), (8, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, ''),
(5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 0, ''), (2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 0, '');

-- ── Alertas ──────────────────────────────────────────────────
INSERT INTO alertas (id_estudiante, tipo_deficiencia, descripcion, nivel, estado) VALUES
(5, 'Hierro', 'Deficiencia de hierro detectada según seguimiento nutricional.', 'alta', 'activa'),
(5, 'Proteinas', 'Bajo aporte proteico en los últimos menús.', 'media', 'activa'),
(2, 'Calcio', 'Cobertura de calcio por debajo del mínimo recomendado.', 'media', 'activa'),
(8, 'Vitamina D', 'Niveles de vitamina D a vigilar.', 'baja', 'activa'),
(0, 'calorias', 'El menú del día cubre solo el 62% de las calorías recomendadas por el ICBF.', 'media', 'activa'),
(3, 'Hierro', 'Alerta resuelta tras ajuste de menú.', 'media', 'resuelta');

-- ── Cobertura nutricional (últimos 7 días) ───────────────────
INSERT INTO cobertura_nutricional (fecha, nutriente, porcentaje) VALUES
(CURDATE(), 'Hierro', 68), (CURDATE(), 'Calcio', 74), (CURDATE(), 'Proteinas', 88),
(CURDATE(), 'Vitamina D', 55), (CURDATE(), 'Zinc', 80),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Hierro', 70), (DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Calcio', 78),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Proteinas', 90), (DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Vitamina D', 60),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Zinc', 82),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Hierro', 65), (DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Calcio', 72),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Proteinas', 85), (DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Vitamina D', 50),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Zinc', 78);

-- ── Riesgo diario (tendencia últimos días) ───────────────────
INSERT INTO riesgo_diario (id_estudiante, fecha, nivel_riesgo, score) VALUES
(5, CURDATE(), 'alto', 72), (2, CURDATE(), 'medio', 45), (8, CURDATE(), 'medio', 50),
(3, CURDATE(), 'bajo', 20), (7, CURDATE(), 'bajo', 18), (1, CURDATE(), 'sin_riesgo', 5),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'alto', 70), (2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'medio', 40),
(8, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'bajo', 35),
(5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'medio', 60), (2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'medio', 42);
