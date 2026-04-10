-- ==============================================================================
-- BASE DE DATOS: goobv-usuarios
-- DESCRIPCIÓN: Gestión de Seguridad, Accesos, Auditoría y Notificaciones
-- ==============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-04:00"; -- Hora de Venezuela

-- --------------------------------------------------------
-- 1. TABLAS DICCIONARIO Y MAESTRAS
-- --------------------------------------------------------

CREATE TABLE `rol` (
  `id_rol` varchar(30) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `idx_rol_nombre` (`nombre_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema';

CREATE TABLE `modulo` (
  `id_modulo` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_modulo`),
  UNIQUE KEY `idx_modulo_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Módulos del sistema para permisos';

-- --------------------------------------------------------
-- 2. TABLA DE IMÁGENES POLIMÓRFICA (Reutilizable)
-- --------------------------------------------------------

CREATE TABLE `imagen` (
  `id_imagen` varchar(30) NOT NULL,
  `entidad_tipo` enum('USUARIO','NOTICIA','PRODUCTO','CATEGORIA','PROMOCION') NOT NULL COMMENT 'Tipo de entidad a la que pertenece la imagen',
  `entidad_id` varchar(30) NOT NULL COMMENT 'ID de la entidad relacionada',
  `direccion` varchar(255) NOT NULL COMMENT 'Ruta o URL de la imagen',
  `orden` int(11) DEFAULT 1 COMMENT 'Orden de visualización',
  `es_principal` tinyint(1) DEFAULT 0 COMMENT 'Indica si es la imagen principal',
  `titulo` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_imagen`),
  KEY `idx_imagen_entidad` (`entidad_tipo`, `entidad_id`),
  KEY `idx_imagen_usuario` (`entidad_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Almacena imágenes para múltiples entidades del sistema';

-- --------------------------------------------------------
-- 3. TABLAS TRANSACCIONALES
-- --------------------------------------------------------

CREATE TABLE `usuario` (
  `cedula` varchar(15) NOT NULL,
  `id_rol` varchar(30) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `clave` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL COMMENT 'DEPRECATED: Usar tabla imagen con entidad_tipo="USUARIO" y es_principal=1',
  `tema` varchar(20) DEFAULT 'light',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cedula`),
  UNIQUE KEY `idx_usuario_username` (`username`),
  UNIQUE KEY `idx_usuario_correo` (`correo`),
  KEY `fk_usuario_rol` (`id_rol`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permiso` (
  `id_permiso` varchar(30) NOT NULL,
  `id_rol` varchar(30) NOT NULL,
  `id_modulo` varchar(30) NOT NULL,
  `accion` varchar(50) NOT NULL COMMENT 'LEER, CREAR, EDITAR, ELIMINAR',
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `idx_permiso_unico` (`id_rol`,`id_modulo`,`accion`),
  KEY `fk_permiso_modulo` (`id_modulo`),
  CONSTRAINT `fk_permiso_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_permiso_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sesion` (
  `id_sesion` varchar(50) NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `dispositivo` varchar(255) DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_sesion`),
  UNIQUE KEY `idx_token` (`token`),
  KEY `fk_sesion_usuario` (`cedula`),
  CONSTRAINT `fk_sesion_usuario` FOREIGN KEY (`cedula`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bitacora` (
  `id_bitacora` varchar(30) NOT NULL,
  `cedula` varchar(15) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `modulo` varchar(50) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `detalle` text NOT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_nuevos`)),
  PRIMARY KEY (`id_bitacora`),
  KEY `idx_bitacora_fecha` (`fecha`),
  KEY `fk_bitacora_usuario` (`cedula`),
  CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`cedula`) REFERENCES `usuario` (`cedula`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notificacion` (
  `id_notificacion` varchar(30) NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('INFO','ALERTA','EXITO','ERROR') DEFAULT 'INFO',
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_leido` timestamp NULL DEFAULT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_notificacion`),
  KEY `fk_notificacion_usuario` (`cedula`),
  CONSTRAINT `fk_notificacion_usuario` FOREIGN KEY (`cedula`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `noticia` (
  `id_noticia` varchar(30) NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `subtitulo` varchar(150) DEFAULT NULL,
  `contenido` text NOT NULL,
  `tipo` enum('INFO','ALERTA','EXITO','ERROR') DEFAULT 'INFO',
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NULL DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_noticia`),
  KEY `fk_noticia_usuario` (`cedula`),
  CONSTRAINT `fk_noticia_usuario` FOREIGN KEY (`cedula`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. VISTAS (VIEWS)
-- --------------------------------------------------------

CREATE VIEW `vw_accesos_usuarios` AS
SELECT u.cedula, r.nombre_rol AS rol, m.nombre AS modulo, p.accion 
FROM usuario u
JOIN rol r ON u.id_rol = r.id_rol
JOIN permiso p ON r.id_rol = p.id_rol
JOIN modulo m ON p.id_modulo = m.id_modulo
WHERE u.estatus = 1 AND p.estatus = 1;

CREATE VIEW `vw_sesiones_activas` AS
SELECT s.id_sesion, u.cedula, r.nombre_rol AS rol, s.ip, s.dispositivo, s.fecha_inicio 
FROM sesion s
JOIN usuario u ON s.cedula = u.cedula
JOIN rol r ON u.id_rol = r.id_rol
WHERE s.estatus = 1 AND s.fecha_expiracion > NOW();

CREATE VIEW `vw_imagenes_usuario` AS
SELECT u.cedula, u.username, u.nombres, u.apellidos, i.direccion, i.es_principal, i.orden
FROM usuario u
LEFT JOIN imagen i ON u.cedula = i.entidad_id AND i.entidad_tipo = 'USUARIO'
WHERE i.es_principal = 1 OR i.es_principal IS NULL;

-- --------------------------------------------------------
-- 5. PROCEDIMIENTOS (STORED PROCEDURES)
-- --------------------------------------------------------

DELIMITER $$
CREATE PROCEDURE `sp_registrar_bitacora`(
    IN `p_cedula` VARCHAR(15), 
    IN `p_modulo` VARCHAR(50), 
    IN `p_accion` VARCHAR(50), 
    IN `p_detalle` TEXT,
    IN `p_old` JSON,
    IN `p_new` JSON
)
BEGIN
    INSERT INTO bitacora (id_bitacora, cedula, modulo, accion, detalle, valores_anteriores, valores_nuevos)
    VALUES (CONCAT('LOG-', UNIX_TIMESTAMP(), '-', SUBSTRING(MD5(RAND()), 1, 4)), p_cedula, p_modulo, p_accion, p_detalle, p_old, p_new);
END$$

-- Procedimiento para obtener imágenes por entidad
CREATE PROCEDURE `sp_obtener_imagenes_entidad`(
    IN `p_tipo` VARCHAR(20),
    IN `p_id` VARCHAR(30)
)
BEGIN
    SELECT id_imagen, direccion, orden, es_principal, titulo, descripcion, fecha_subida
    FROM imagen 
    WHERE entidad_tipo = p_tipo AND entidad_id = p_id
    ORDER BY es_principal DESC, orden ASC;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- 6. DISPARADORES (TRIGGERS)
-- --------------------------------------------------------

DELIMITER $$
CREATE TRIGGER `trg_audit_usuario_update` AFTER UPDATE ON `usuario`
FOR EACH ROW BEGIN
    IF OLD.estatus != NEW.estatus THEN
        CALL sp_registrar_bitacora(NEW.cedula, 'SEGURIDAD', 'UPDATE_ESTATUS', CONCAT('Estatus cambiado de ', OLD.estatus, ' a ', NEW.estatus), NULL, NULL);
    END IF;
END$$

-- Trigger para asegurar solo una imagen principal por entidad
CREATE TRIGGER `trg_imagen_principal_unica` BEFORE INSERT ON `imagen`
FOR EACH ROW BEGIN
    IF NEW.es_principal = 1 THEN
        UPDATE imagen SET es_principal = 0 
        WHERE entidad_tipo = NEW.entidad_tipo 
        AND entidad_id = NEW.entidad_id 
        AND es_principal = 1;
    END IF;
END$$

DELIMITER ;

COMMIT;