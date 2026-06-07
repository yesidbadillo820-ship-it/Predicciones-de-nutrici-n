-- Usuario de aplicación con acceso a cada base por servicio.
-- (En producción, lo ideal es un usuario distinto por microservicio.)
CREATE USER IF NOT EXISTS 'nutri'@'%' IDENTIFIED BY 'nutri';
GRANT ALL PRIVILEGES ON db_auth.*        TO 'nutri'@'%';
GRANT ALL PRIVILEGES ON db_estudiantes.* TO 'nutri'@'%';
GRANT ALL PRIVILEGES ON db_alimentos.*   TO 'nutri'@'%';
GRANT ALL PRIVILEGES ON db_menus.*       TO 'nutri'@'%';
GRANT ALL PRIVILEGES ON db_asistencia.*  TO 'nutri'@'%';
GRANT ALL PRIVILEGES ON db_alertas.*     TO 'nutri'@'%';
GRANT ALL PRIVILEGES ON db_predictivo.*  TO 'nutri'@'%';
FLUSH PRIVILEGES;
