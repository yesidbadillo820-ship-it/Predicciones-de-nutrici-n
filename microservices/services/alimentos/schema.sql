-- Base de datos PROPIA del microservicio Alimentos.
CREATE DATABASE IF NOT EXISTS db_alimentos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_alimentos;

CREATE TABLE IF NOT EXISTS alimentos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(120) NOT NULL,
    categoria       VARCHAR(60) NOT NULL DEFAULT 'General',
    calorias        DECIMAL(8,2) NOT NULL DEFAULT 0,
    proteinas_g     DECIMAL(8,2) NOT NULL DEFAULT 0,
    carbohidratos_g DECIMAL(8,2) NOT NULL DEFAULT 0,
    grasas_g        DECIMAL(8,2) NOT NULL DEFAULT 0,
    hierro_mg       DECIMAL(8,2) NOT NULL DEFAULT 0,
    calcio_mg       DECIMAL(8,2) NOT NULL DEFAULT 0,
    vitamina_d_ug   DECIMAL(8,2) NOT NULL DEFAULT 0,
    zinc_mg         DECIMAL(8,2) NOT NULL DEFAULT 0,
    activo          TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO alimentos (id,nombre,categoria,calorias,proteinas_g,carbohidratos_g,grasas_g,hierro_mg,calcio_mg,vitamina_d_ug,zinc_mg) VALUES
(1,'Arroz blanco cocido','Cereales',130,2.7,28.0,0.3,0.2,10,0.0,0.5),
(2,'Frijol rojo cocido','Leguminosas',127,8.7,22.8,0.5,2.9,35,0.0,1.0),
(3,'Lentejas cocidas','Leguminosas',116,9.0,20.1,0.4,3.3,19,0.0,1.3),
(4,'Pollo pechuga','Proteínas',165,31.0,0.0,3.6,0.7,15,0.1,1.0),
(5,'Huevo cocido','Proteínas',155,13.0,1.1,11.0,1.2,50,2.0,1.3),
(6,'Leche entera','Lácteos',61,3.2,4.8,3.3,0.0,113,1.3,0.4),
(7,'Espinaca cocida','Verduras',23,3.0,3.8,0.3,3.6,99,0.0,0.8),
(8,'Plátano maduro','Frutas',89,1.1,22.8,0.3,0.3,5,0.0,0.2),
(9,'Arepa de maíz','Cereales',219,5.0,45.0,2.5,1.5,90,0.0,0.7),
(10,'Sardina en lata','Proteínas',208,25.0,0.0,11.0,2.9,382,4.8,1.3);
