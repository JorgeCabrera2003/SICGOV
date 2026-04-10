-- ==============================================================================
-- BASE DE DATOS: goobv-sistema
-- DESCRIPCIÓN: Lógica de Negocio, Inventario, Pedidos, RRHH y Mesas
-- ==============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-04:00";

-- --------------------------------------------------------
-- 1. TABLAS DICCIONARIO / CATÁLOGOS BASE
-- --------------------------------------------------------

CREATE TABLE `cargo` (
  `id_cargo` varchar(30) NOT NULL,
  `nombre_cargo` varchar(60) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tipo_permiso` (
  `id_tipo_permiso` varchar(30) NOT NULL,
  `nombre` varchar(60) NOT NULL COMMENT 'Ej: Reposo médico, Vacaciones, Personal',
  PRIMARY KEY (`id_tipo_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `turno` (
  `id_turno` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `minuto_tolerancia` int(11) DEFAULT 15,
  PRIMARY KEY (`id_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categoria_ingrediente` (
  `id_categoria` varchar(30) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categoria_producto` (
  `id_categoria` varchar(30) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(255) DEFAULT 'default.png',
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `idx_categoria_producto_nombre` (`nombre_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `area_mesa` (
  `id_area` varchar(30) NOT NULL,
  `nombre` varchar(60) NOT NULL COMMENT 'Ej: Terraza, VIP, Salón Principal',
  `descripcion` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `metodo_pago` (
  `id_metodo_pago` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_metodo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. PERSONAS Y ENTIDADES
-- --------------------------------------------------------

CREATE TABLE `persona` (
  `cedula` varchar(15) NOT NULL,
  `documento` varchar(20) DEFAULT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  PRIMARY KEY (`cedula`),
  UNIQUE KEY `idx_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `empleado` (
  `cedula` varchar(15) NOT NULL,
  `id_cargo` varchar(30) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  PRIMARY KEY (`cedula`),
  KEY `fk_emp_cargo` (`id_cargo`),
  CONSTRAINT `fk_emp_persona` FOREIGN KEY (`cedula`) REFERENCES `persona` (`cedula`) ON DELETE CASCADE,
  CONSTRAINT `fk_emp_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal` (
  `cedula_personal` varchar(15) NOT NULL,
  `nombres` varchar(80) NOT NULL,
  `apellidos` varchar(80) NOT NULL,
  `id_cargo` varchar(30) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `salario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cedula_personal`),
  KEY `fk_personal_cargo` (`id_cargo`),
  CONSTRAINT `fk_personal_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cliente` (
  `cedula` varchar(15) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_visita` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cedula`),
  CONSTRAINT `fk_cli_persona` FOREIGN KEY (`cedula`) REFERENCES `persona` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `proveedor` (
  `documento_legal` varchar(30) NOT NULL COMMENT 'RIF o Cédula',
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`documento_legal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. RECURSOS HUMANOS
-- --------------------------------------------------------

CREATE TABLE `planificador_turno` (
  `id_planificador` varchar(30) NOT NULL,
  `cedula_empleado` varchar(15) NOT NULL,
  `id_turno` varchar(30) NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id_planificador`),
  KEY `fk_plan_emp` (`cedula_empleado`),
  KEY `fk_plan_turno` (`id_turno`),
  CONSTRAINT `fk_plan_emp` FOREIGN KEY (`cedula_empleado`) REFERENCES `empleado` (`cedula`) ON DELETE CASCADE,
  CONSTRAINT `fk_plan_turno` FOREIGN KEY (`id_turno`) REFERENCES `turno` (`id_turno`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `asistencia` (
  `id_asistencia` varchar(30) NOT NULL,
  `cedula_empleado` varchar(15) NOT NULL,
  `tipo_marcacion` enum('ENTRADA','SALIDA','DESCANSO_OUT','DESCANSO_IN') NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` enum('A_TIEMPO','TARDE','FALTA') DEFAULT 'A_TIEMPO',
  `observacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  KEY `fk_asis_emp` (`cedula_empleado`),
  CONSTRAINT `fk_asis_emp` FOREIGN KEY (`cedula_empleado`) REFERENCES `empleado` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permiso_laboral` (
  `id_permiso` varchar(30) NOT NULL,
  `id_tipo_permiso` varchar(30) NOT NULL,
  `cedula_empleado` varchar(15) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','APROBADO','RECHAZADO') DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_permiso`),
  KEY `fk_perm_tipo` (`id_tipo_permiso`),
  KEY `fk_perm_emp` (`cedula_empleado`),
  CONSTRAINT `fk_perm_tipo` FOREIGN KEY (`id_tipo_permiso`) REFERENCES `tipo_permiso` (`id_tipo_permiso`) ON UPDATE CASCADE,
  CONSTRAINT `fk_perm_emp` FOREIGN KEY (`cedula_empleado`) REFERENCES `empleado` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. INVENTARIO Y CATÁLOGO
-- --------------------------------------------------------

CREATE TABLE `ingrediente` (
  `id_ingrediente` varchar(30) NOT NULL,
  `id_categoria` varchar(30) NOT NULL,
  `nombre_ingrediente` varchar(100) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT 0.00,
  `stock_actual` decimal(10,3) NOT NULL DEFAULT 0.000,
  `stock_minimo` decimal(10,3) NOT NULL DEFAULT 0.000,
  `stock_maximo` decimal(10,3) DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_ingrediente`),
  KEY `fk_ing_cat` (`id_categoria`),
  CONSTRAINT `fk_ing_cat` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_ingrediente` (`id_categoria`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `producto` (
  `id_producto` varchar(30) NOT NULL,
  `id_categoria` varchar(30) DEFAULT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL CHECK (`precio` >= 0),
  `costo_preparacion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 1,
  `tiempo_preparacion` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT 'default-product.png',
  `es_personalizable` tinyint(1) NOT NULL DEFAULT 0,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  `tipo_producto` enum('COCINA','BARRA','POSTRE','RETAIL') NOT NULL DEFAULT 'COCINA',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_producto`),
  KEY `fk_prod_cat` (`id_categoria`),
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_producto` (`id_categoria`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `preparacion` (
  `id_preparacion` varchar(30) NOT NULL,
  `id_producto` varchar(30) NOT NULL,
  `id_ingrediente` varchar(30) NOT NULL,
  `prioridad_ingrediente` int(11) DEFAULT 1,
  `cantidad` decimal(10,3) NOT NULL CHECK (`cantidad` > 0),
  `unidad_medida` varchar(20) NOT NULL,
  PRIMARY KEY (`id_preparacion`),
  KEY `fk_prep_prod` (`id_producto`),
  KEY `fk_prep_ing` (`id_ingrediente`),
  CONSTRAINT `fk_prep_prod` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE,
  CONSTRAINT `fk_prep_ing` FOREIGN KEY (`id_ingrediente`) REFERENCES `ingrediente` (`id_ingrediente`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recetas de productos';

CREATE TABLE `promocion` (
  `id_promocion` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo_descuento` enum('PORCENTAJE','MONTO_FIJO') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  PRIMARY KEY (`id_promocion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `planificador_promocion` (
  `id_planificador` varchar(30) NOT NULL,
  `id_producto` varchar(30) NOT NULL,
  `id_promocion` varchar(30) NOT NULL,
  PRIMARY KEY (`id_planificador`),
  KEY `fk_planp_prod` (`id_producto`),
  KEY `fk_planp_prom` (`id_promocion`),
  CONSTRAINT `fk_planp_prod` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE,
  CONSTRAINT `fk_planp_prom` FOREIGN KEY (`id_promocion`) REFERENCES `promocion` (`id_promocion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. OPERACIONES (MESAS Y PEDIDOS)
-- --------------------------------------------------------

CREATE TABLE `mesa` (
  `id_mesa` varchar(30) NOT NULL,
  `id_area` varchar(30) DEFAULT NULL,
  `numero_mesa` int(11) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `estado` enum('DISPONIBLE','LIBRE','OCUPADA','MANTENIMIENTO') DEFAULT 'DISPONIBLE',
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_mesa`),
  UNIQUE KEY `idx_mesa_area_num` (`id_area`,`numero_mesa`),
  CONSTRAINT `fk_mesa_area` FOREIGN KEY (`id_area`) REFERENCES `area_mesa` (`id_area`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservacion` (
  `id_reservacion` varchar(30) NOT NULL,
  `cedula_cliente` varchar(15) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` enum('PENDIENTE','CONFIRMADA','CANCELADA','COMPLETADA') DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_reservacion`),
  KEY `fk_res_cli` (`cedula_cliente`),
  CONSTRAINT `fk_res_cli` FOREIGN KEY (`cedula_cliente`) REFERENCES `cliente` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `asignacion_mesa` (
  `id_asignacion` varchar(30) NOT NULL,
  `id_reservacion` varchar(30) NOT NULL,
  `id_mesa` varchar(30) NOT NULL,
  PRIMARY KEY (`id_asignacion`),
  KEY `fk_am_res` (`id_reservacion`),
  KEY `fk_am_mesa` (`id_mesa`),
  CONSTRAINT `fk_am_res` FOREIGN KEY (`id_reservacion`) REFERENCES `reservacion` (`id_reservacion`) ON DELETE CASCADE,
  CONSTRAINT `fk_am_mesa` FOREIGN KEY (`id_mesa`) REFERENCES `mesa` (`id_mesa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pedido` (
  `id_pedido` varchar(30) NOT NULL,
  `cedula_cliente` varchar(15) DEFAULT NULL,
  `cedula_empleado` varchar(15) NOT NULL,
  `id_mesa` varchar(30) DEFAULT NULL,
  `tipo_pedido` enum('MESA','LLEVAR','DELIVERY') NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','COCINANDO','LISTO','ENTREGADO','PAGADO','CANCELADO') DEFAULT 'PENDIENTE',
  `observacion` varchar(255) DEFAULT NULL,
  `impuesto` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_pedido`),
  KEY `fk_ped_cli` (`cedula_cliente`),
  KEY `fk_ped_emp` (`cedula_empleado`),
  KEY `fk_ped_mesa` (`id_mesa`),
  CONSTRAINT `fk_ped_cli` FOREIGN KEY (`cedula_cliente`) REFERENCES `cliente` (`cedula`) ON DELETE SET NULL,
  CONSTRAINT `fk_ped_emp` FOREIGN KEY (`cedula_empleado`) REFERENCES `empleado` (`cedula`),
  CONSTRAINT `fk_ped_mesa` FOREIGN KEY (`id_mesa`) REFERENCES `mesa` (`id_mesa`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `detalle_pedido` (
  `id_detalle` varchar(30) NOT NULL,
  `id_pedido` varchar(30) NOT NULL,
  `id_producto` varchar(30) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unitario` decimal(10,2) NOT NULL,
  `indicacion` varchar(255) DEFAULT NULL COMMENT 'Ej: Sin cebolla, bien cocido',
  PRIMARY KEY (`id_detalle`),
  KEY `fk_det_ped` (`id_pedido`),
  KEY `fk_det_prod` (`id_producto`),
  CONSTRAINT `fk_det_ped` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_det_prod` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pago` (
  `id_pago` varchar(30) NOT NULL,
  `id_pedido` varchar(30) NOT NULL,
  `id_metodo_pago` varchar(30) NOT NULL,
  `monto` decimal(10,2) NOT NULL CHECK (`monto` > 0),
  `tasa_actual` decimal(10,2) DEFAULT 1.00,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago`),
  KEY `fk_pago_ped` (`id_pedido`),
  KEY `fk_pago_metodo` (`id_metodo_pago`),
  CONSTRAINT `fk_pago_ped` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_pago_metodo` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodo_pago` (`id_metodo_pago`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. MOVIMIENTOS DE INVENTARIO
-- --------------------------------------------------------

CREATE TABLE `entrada_ingrediente` (
  `id_entrada` varchar(30) NOT NULL,
  `id_ingrediente` varchar(30) NOT NULL,
  `documento_proveedor` varchar(30) NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_entrada`),
  KEY `fk_ent_ing` (`id_ingrediente`),
  KEY `fk_ent_prov` (`documento_proveedor`),
  CONSTRAINT `fk_ent_ing` FOREIGN KEY (`id_ingrediente`) REFERENCES `ingrediente` (`id_ingrediente`) ON DELETE CASCADE,
  CONSTRAINT `fk_ent_prov` FOREIGN KEY (`documento_proveedor`) REFERENCES `proveedor` (`documento_legal`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `movimiento_ingrediente` (
  `id_movimiento` varchar(30) NOT NULL,
  `id_ingrediente` varchar(30) NOT NULL,
  `id_detalle` varchar(30) DEFAULT NULL COMMENT 'Vinculado a la venta si es una salida automática',
  `cantidad` decimal(10,3) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `tipo` enum('ENTRADA','SALIDA','AJUSTE','MERMA') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  KEY `fk_mov_ing` (`id_ingrediente`),
  KEY `fk_mov_det` (`id_detalle`),
  CONSTRAINT `fk_mov_ing` FOREIGN KEY (`id_ingrediente`) REFERENCES `ingrediente` (`id_ingrediente`) ON DELETE CASCADE,
  CONSTRAINT `fk_mov_det` FOREIGN KEY (`id_detalle`) REFERENCES `detalle_pedido` (`id_detalle`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. VISTAS (VIEWS)
-- --------------------------------------------------------

-- Vista 1: Ingredientes por debajo del stock mínimo
CREATE VIEW `vw_alertas_inventario` AS
SELECT i.id_ingrediente, i.nombre_ingrediente AS nombre, c.nombre AS categoria, i.stock_actual, i.stock_minimo, i.unidad_medida 
FROM ingrediente i
JOIN categoria_ingrediente c ON i.id_categoria = c.id_categoria
WHERE i.stock_actual <= i.stock_minimo;

-- Vista 2: Resumen de empleados con su cargo
CREATE VIEW `vw_directorio_empleados` AS
SELECT p.cedula, p.nombre, p.apellido, p.telefono, c.nombre_cargo AS cargo, e.fecha_ingreso 
FROM empleado e
JOIN persona p ON e.cedula = p.cedula
JOIN cargo c ON e.id_cargo = c.id_cargo;

-- --------------------------------------------------------
-- 8. DISPARADORES (TRIGGERS)
-- --------------------------------------------------------

DELIMITER $$

-- Trigger: Actualizar el stock_actual del ingrediente cada vez que hay un movimiento
CREATE TRIGGER `trg_actualizar_stock_movimiento` AFTER INSERT ON `movimiento_ingrediente`
FOR EACH ROW BEGIN
    IF NEW.tipo = 'ENTRADA' OR NEW.tipo = 'AJUSTE' THEN
        UPDATE ingrediente SET stock_actual = stock_actual + NEW.cantidad WHERE id_ingrediente = NEW.id_ingrediente;
    ELSEIF NEW.tipo = 'SALIDA' OR NEW.tipo = 'MERMA' THEN
        UPDATE ingrediente SET stock_actual = stock_actual - NEW.cantidad WHERE id_ingrediente = NEW.id_ingrediente;
    END IF;
END$$

-- Trigger: Actualizar la última visita del cliente cuando hace un pedido
CREATE TRIGGER `trg_actualizar_visita_cliente` AFTER INSERT ON `pedido`
FOR EACH ROW BEGIN
    IF NEW.cedula_cliente IS NOT NULL THEN
        UPDATE cliente SET ultima_visita = NEW.fecha_pedido WHERE cedula = NEW.cedula_cliente;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- 9. PROCEDIMIENTOS (STORED PROCEDURES)
-- --------------------------------------------------------

DELIMITER $$

-- Procedimiento Profesional: Descontar inventario automáticamente al vender un Detalle de Pedido
-- Se ejecuta cuando el detalle pasa a cocina o se entrega.
CREATE PROCEDURE `sp_descontar_receta_pedido`(IN `p_id_detalle` VARCHAR(30))
BEGIN
    DECLARE v_done INT DEFAULT FALSE;
    DECLARE v_id_ingrediente VARCHAR(30);
    DECLARE v_cantidad_receta DECIMAL(10,3);
    DECLARE v_cantidad_pedida INT;
    DECLARE v_unidad VARCHAR(20);
    
    -- Cursor para recorrer los ingredientes de la receta del producto pedido
    DECLARE cur_receta CURSOR FOR 
        SELECT pr.id_ingrediente, pr.cantidad, pr.unidad_medida, dp.cantidad
        FROM preparacion pr
        JOIN detalle_pedido dp ON pr.id_producto = dp.id_producto
        WHERE dp.id_detalle = p_id_detalle;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;
    
    OPEN cur_receta;
    
    read_loop: LOOP
        FETCH cur_receta INTO v_id_ingrediente, v_cantidad_receta, v_unidad, v_cantidad_pedida;
        IF v_done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insertamos el movimiento (el trigger de movimiento se encarga de restar el stock real)
        INSERT INTO movimiento_ingrediente (id_movimiento, id_ingrediente, id_detalle, cantidad, unidad_medida, tipo, descripcion)
        VALUES (CONCAT('MOV-', UNIX_TIMESTAMP(), '-', FLOOR(RAND()*1000)), v_id_ingrediente, p_id_detalle, (v_cantidad_receta * v_cantidad_pedida), v_unidad, 'SALIDA', 'Descuento automático por pedido');
        
    END LOOP;
    
    CLOSE cur_receta;
END$$

DELIMITER ;

COMMIT;