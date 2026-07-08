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

CREATE TABLE `unidad_medida` (
  `id_unidad` varchar(30) NOT NULL,
  `nombre` varchar(30) NOT NULL COMMENT 'kg, g, litros, ml, unidades, etc.',
  `abreviatura` varchar(10) NOT NULL,
  `tipo` enum('PESO','VOLUMEN','UNIDAD','LONGITUD') NOT NULL DEFAULT 'UNIDAD',
  `factor_conversion` decimal(10,6) DEFAULT 1.000000 COMMENT 'Factor para conversión a unidad base',
  `unidad_base` varchar(10) DEFAULT NULL COMMENT 'ID de la unidad base para conversiones',
  PRIMARY KEY (`id_unidad`),
  UNIQUE KEY `idx_unidad_nombre` (`nombre`),
  UNIQUE KEY `idx_unidad_abrev` (`abreviatura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de unidades de medida estandarizadas';

INSERT INTO `unidad_medida` (`id_unidad`, `nombre`, `abreviatura`, `tipo`, `factor_conversion`, `unidad_base`) VALUES
('MEDIAKG23220260519200547232', 'Kilogramo', 'Kg', 'PESO', 1.000000, NULL),
('MEDIAGR23220260519200547232', 'Gramo', 'g', 'PESO', 0.001000, 'Kg'),
('MEDIALB23220260519200547232', 'Libra', 'lb', 'PESO', 0.453592, 'Kg'),
('MEDIAOZ23220260519200547232', 'Onza', 'oz', 'PESO', 0.028350, 'Kg'),
('MEDIALL23220260519200547232', 'Litro', 'L', 'VOLUMEN', 1.000000, NULL),
('MEDIAML23220260519200547232', 'Mililitro', 'ml', 'VOLUMEN', 0.001000, 'L'),
('MEDIAGA23220260519200547232', 'Galón', 'gal', 'VOLUMEN', 3.785410, 'L'),
('MEDIAUN23220260519200547232', 'Unidad', 'U', 'UNIDAD', 1.000000, NULL),
('MEDIAMT23220260519200547232', 'Metro', 'm', 'LONGITUD', 1.000000, 'm'),
('MEDIACE23220260519200547232', 'Centímetro', 'cm', 'LONGITUD', 0.010000, 'm');

CREATE TABLE `cargo` (
  `id_cargo` varchar(30) NOT NULL,
  `nombre_cargo` varchar(60) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tipo_permiso` (
  `id_tipo_permiso` varchar(30) NOT NULL,
  `nombre` varchar(60) NOT NULL COMMENT 'Ej: Reposo médico, Vacaciones, Personal',
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_tipo_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `turno` (
  `id_turno` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `minuto_tolerancia` int(11) DEFAULT 15,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categoria_insumo` (
  `id_categoria` varchar(30) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categoria_producto` (
  `id_categoria` varchar(30) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `idx_categoria_producto_nombre` (`nombre_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `area_mesa` (
  `id_area` varchar(30) NOT NULL,
  `nombre` varchar(60) NOT NULL COMMENT 'Ej: Terraza, VIP, Salón Principal',
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `metodo_pago` (
  `id_metodo_pago` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
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
  `correo` varchar(254) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  PRIMARY KEY (`cedula`),
  UNIQUE KEY `idx_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `empleado` (
  `cedula` varchar(15) NOT NULL,
  `id_cargo` varchar(30) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
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
  `fecha_egreso` date DEFAULT NULL,
  `salario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cedula_personal`),
  KEY `fk_personal_cargo` (`id_cargo`),
  CONSTRAINT `fk_personal_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cliente` (
  `cedula` varchar(15) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cedula`),
  CONSTRAINT `fk_cli_persona` FOREIGN KEY (`cedula`) REFERENCES `persona` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `proveedor` (
  `documento_legal` varchar(30) NOT NULL COMMENT 'RIF o Cédula',
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`documento_legal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. RECURSOS HUMANOS
-- --------------------------------------------------------

CREATE TABLE `planificador_turno` (
  `id_planificador_turno` varchar(30) NOT NULL,
  `cedula_empleado` varchar(15) NOT NULL,
  `id_turno` varchar(30) NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id_planificador_turno`),
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
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_permiso`),
  KEY `fk_perm_tipo` (`id_tipo_permiso`),
  KEY `fk_perm_emp` (`cedula_empleado`),
  CONSTRAINT `fk_perm_tipo` FOREIGN KEY (`id_tipo_permiso`) REFERENCES `tipo_permiso` (`id_tipo_permiso`) ON UPDATE CASCADE,
  CONSTRAINT `fk_perm_emp` FOREIGN KEY (`cedula_empleado`) REFERENCES `empleado` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. INVENTARIO Y CATÁLOGO
-- --------------------------------------------------------

CREATE TABLE `insumo` (
  `id_insumo` varchar(30) NOT NULL,
  `id_categoria` varchar(30) NOT NULL,
  `nombre_insumo` varchar(100) NOT NULL,
  `id_unidad_medida` varchar(30) NOT NULL DEFAULT 'MEDIAUN23220260519200547232',
  `precio_unitario` decimal(10,2) DEFAULT 0.00,
  `stock_actual` decimal(14,8) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(14,8) NOT NULL DEFAULT 0.00,
  `stock_maximo` decimal(14,8) DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_insumo`),
  KEY `fk_ing_cat` (`id_categoria`),
  KEY `fk_ing_unidad` (`id_unidad_medida`),
  CONSTRAINT `fk_ing_cat` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_insumo` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ing_unidad` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidad_medida` (`id_unidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `producto` (
  `id_producto` varchar(30) NOT NULL,
  `id_categoria` varchar(30) DEFAULT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL CHECK (`precio` >= 0),
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
  `id_insumo` varchar(30) NOT NULL,
  `prioridad_insumo` int(11) DEFAULT 1,
  `cantidad` decimal(14,8) NOT NULL CHECK (`cantidad` > 0),
  `id_unidad_medida` varchar(30) NOT NULL DEFAULT 'MEDIAUN23220260519200547232',
  `precio_insumo` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_preparacion`),
  KEY `fk_prep_prod` (`id_producto`),
  KEY `fk_prep_ins` (`id_insumo`),
  KEY `fk_prep_unidad` (`id_unidad_medida`),
  CONSTRAINT `fk_prep_prod` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE,
  CONSTRAINT `fk_prep_ins` FOREIGN KEY (`id_insumo`) REFERENCES `insumo` (`id_insumo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_prep_unidad` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidad_medida` (`id_unidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recetas de productos';

CREATE TABLE `promocion` (
  `id_promocion` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo_descuento` enum('PORCENTAJE','MONTO_FIJO') NOT NULL,
  `valor_descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
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
  `hora_fin` time NOT NULL,
  `id_mesa` varchar(30) DEFAULT NULL,
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
  `numero_pedido` int(11) NOT NULL AUTO_INCREMENT UNIQUE,
  `cedula_cliente` varchar(15) DEFAULT NULL,
  `cedula_empleado` varchar(15) DEFAULT NULL,  -- <-- AHORA PERMITE NULL
  `id_mesa` varchar(30) DEFAULT NULL,
  `tipo_pedido` enum('MESA','LLEVAR','DELIVERY') NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','PREPARANDO','LISTO','ENTREGADO','PAGADO','CANCELADO') DEFAULT 'PENDIENTE',
  `observacion` varchar(255) DEFAULT NULL,
  `impuesto` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_pedido`),
  UNIQUE KEY `idx_numero_pedido` (`numero_pedido`),
  KEY `fk_ped_cli` (`cedula_cliente`),
  KEY `fk_ped_emp` (`cedula_empleado`),
  KEY `fk_ped_mesa` (`id_mesa`),
  CONSTRAINT `fk_ped_cli` FOREIGN KEY (`cedula_cliente`) REFERENCES `cliente` (`cedula`) ON DELETE SET NULL,
  CONSTRAINT `fk_ped_emp` FOREIGN KEY (`cedula_empleado`) REFERENCES `empleado` (`cedula`) ON DELETE SET NULL,  -- <-- ON DELETE SET NULL
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

CREATE TABLE `entrada_insumo` (
  `id_entrada` varchar(30) NOT NULL,
  `id_insumo` varchar(30) NOT NULL,
  `documento_proveedor` varchar(30) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_entrada`),
  KEY `fk_ent_ins` (`id_insumo`),
  KEY `fk_ent_prov` (`documento_proveedor`),
  CONSTRAINT `fk_ent_ins` FOREIGN KEY (`id_insumo`) REFERENCES `insumo` (`id_insumo`) ON DELETE CASCADE,
  CONSTRAINT `fk_ent_prov` FOREIGN KEY (`documento_proveedor`) REFERENCES `proveedor` (`documento_legal`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `detalle_entrada` (
  `id_detalle` varchar(30) NOT NULL,
  `id_entrada` varchar(30) NOT NULL,
  `id_unidad_medida` varchar(30) NOT NULL,
  `cantidad` decimal(14,8) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  KEY `fk_ent` (`id_entrada`),
  KEY `fk_uni` (`id_unidad_medida`),
  CONSTRAINT `fk_ent` FOREIGN KEY (`id_entrada`) REFERENCES `entrada_insumo` (`id_entrada`) ON DELETE CASCADE,
  CONSTRAINT `fk_uni` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidad_medida` (`id_unidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `movimiento_insumo` (
  `id_movimiento` varchar(30) NOT NULL,
  `id_insumo` varchar(30) NOT NULL,
  `id_detalle` varchar(30) DEFAULT NULL COMMENT 'Vinculado a la venta si es una salida automática',
  `cantidad` decimal(14,8) NOT NULL,
  `id_unidad_medida` varchar(30) NOT NULL DEFAULT 'MEDIAUN23220260519200547232',
  `tipo` enum('ENTRADA','SALIDA','AJUSTE','MERMA') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  KEY `fk_mov_ing` (`id_insumo`),
  KEY `fk_mov_det` (`id_detalle`),
  KEY `fk_mov_unidad` (`id_unidad_medida`),
  CONSTRAINT `fk_mov_ing` FOREIGN KEY (`id_insumo`) REFERENCES `insumo` (`id_insumo`) ON DELETE CASCADE,
  CONSTRAINT `fk_mov_det` FOREIGN KEY (`id_detalle`) REFERENCES `detalle_pedido` (`id_detalle`) ON DELETE SET NULL,
  CONSTRAINT `fk_mov_unidad` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidad_medida` (`id_unidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. VISTAS (VIEWS)
-- --------------------------------------------------------

CREATE VIEW `vw_alertas_inventario` AS
SELECT i.id_insumo, i.nombre_insumo AS nombre, c.nombre AS categoria, 
       i.stock_actual, i.stock_minimo, um.nombre AS unidad_medida
FROM insumo i
JOIN categoria_insumo c ON i.id_categoria = c.id_categoria
JOIN unidad_medida um ON i.id_unidad_medida = um.id_unidad
WHERE i.stock_actual <= i.stock_minimo;

CREATE VIEW `vw_directorio_empleados` AS
SELECT p.cedula, p.nombre, p.apellido, p.telefono, c.nombre_cargo AS cargo, e.fecha_ingreso
FROM empleado e
JOIN persona p ON e.cedula = p.cedula
JOIN cargo c ON e.id_cargo = c.id_cargo;

CREATE VIEW `vw_conversiones_unidades` AS
SELECT u1.id_unidad, u1.nombre, u1.abreviatura, u1.tipo,
       u1.factor_conversion, u2.nombre AS unidad_base_nombre
FROM unidad_medida u1
LEFT JOIN unidad_medida u2 ON u1.unidad_base = u2.id_unidad;

CREATE VIEW vw_insumo AS
SELECT i.id_insumo,
i.nombre_insumo,
i.id_categoria,
ci.nombre AS nombre_categoria,
i.id_unidad_medida,
u.nombre AS unidad_medida,
u.abreviatura,
i.precio_unitario,
i.stock_actual,
i.stock_minimo,
i.stock_maximo,
i.estatus FROM insumo AS i
INNER JOIN unidad_medida AS u ON i.id_unidad_medida = u.id_unidad
INNER JOIN categoria_insumo AS ci ON i.id_categoria = ci.id_categoria;

CREATE VIEW `vw_entrada_insumo` AS 
SELECT `ei`.*, `in`.`nombre_insumo` AS 'insumo', `p`.nombre AS 'proveedor' FROM `entrada_insumo` AS `ei`
INNER JOIN `insumo` AS `in` ON `ei`.id_insumo = `in`.id_insumo
INNER JOIN `proveedor`AS `p` ON `ei`.`documento_proveedor` = `p`.documento_legal;

CREATE VIEW `vw_detalle_entrada_insumo` AS 
SELECT `de`.`id_detalle`,`de`.`fecha`, `de`.`cantidad`, `de`.`descripcion`,
`i`.`nombre_insumo` AS `insumo`, `i`.`id_insumo`, `p`.`nombre` AS `proveedor`, `i`.`stock_actual`
FROM `detalle_entrada` AS `de`
INNER JOIN `entrada_insumo` AS `ei` ON `ei`.`id_entrada` = `de`.`id_entrada`
INNER JOIN `unidad_medida` AS `um` ON `um`.`id_unidad` = `de`.`id_unidad_medida`
INNER JOIN `proveedor` AS `p` ON `p`.`documento_legal` = `ei`.`documento_proveedor`
INNER JOIN `insumo` AS `i` ON `i`.`id_insumo` = `ei`.`id_insumo`;

-- --------------------------------------------------------
-- 8. DISPARADORES (TRIGGERS)
-- --------------------------------------------------------

DELIMITER $$

CREATE TRIGGER `trg_actualizar_stock_movimiento` AFTER INSERT ON `movimiento_insumo`
FOR EACH ROW BEGIN
    IF NEW.tipo = 'ENTRADA' OR NEW.tipo = 'AJUSTE' THEN
        UPDATE insumo SET stock_actual = stock_actual + NEW.cantidad WHERE id_insumo = NEW.id_insumo;
    ELSEIF NEW.tipo = 'SALIDA' OR NEW.tipo = 'MERMA' THEN
        UPDATE insumo SET stock_actual = stock_actual - NEW.cantidad WHERE id_insumo = NEW.id_insumo;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- 9. PROCEDIMIENTOS (STORED PROCEDURES)
-- --------------------------------------------------------

DELIMITER $$

CREATE PROCEDURE `sp_descontar_receta_pedido`(IN `p_id_detalle` VARCHAR(30))
BEGIN
    DECLARE v_done INT DEFAULT FALSE;
    DECLARE v_id_ingrediente VARCHAR(30);
    DECLARE v_cantidad_receta DECIMAL(10,3);
    DECLARE v_cantidad_pedida INT;
    DECLARE v_id_unidad VARCHAR(10);
    
    DECLARE cur_receta CURSOR FOR 
        SELECT pr.id_ingrediente, pr.cantidad, pr.id_unidad_medida, dp.cantidad
        FROM preparacion pr
        JOIN detalle_pedido dp ON pr.id_producto = dp.id_producto
        WHERE dp.id_detalle = p_id_detalle;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;
    
    OPEN cur_receta;
    
    read_loop: LOOP
        FETCH cur_receta INTO v_id_ingrediente, v_cantidad_receta, v_id_unidad, v_cantidad_pedida;
        IF v_done THEN
            LEAVE read_loop;
        END IF;
        
        INSERT INTO movimiento_ingrediente (id_movimiento, id_ingrediente, id_detalle, cantidad, id_unidad_medida, tipo, descripcion)
        VALUES (CONCAT('MOV-', UNIX_TIMESTAMP(), '-', FLOOR(RAND()*1000)), v_id_ingrediente, p_id_detalle, (v_cantidad_receta * v_cantidad_pedida), v_id_unidad, 'SALIDA', 'Descuento automático por pedido');
        
    END LOOP;
    
    CLOSE cur_receta;
END$$

CREATE PROCEDURE `sp_convertir_unidad`(
    IN `p_cantidad` DECIMAL(10,3),
    IN `p_unidad_origen` VARCHAR(10),
    IN `p_unidad_destino` VARCHAR(10),
    OUT `p_resultado` DECIMAL(10,3)
)
BEGIN
    DECLARE v_factor_origen DECIMAL(10,6);
    DECLARE v_factor_destino DECIMAL(10,6);
    DECLARE v_tipo_origen VARCHAR(20);
    DECLARE v_tipo_destino VARCHAR(20);
    
    SELECT factor_conversion, tipo INTO v_factor_origen, v_tipo_origen
    FROM unidad_medida WHERE id_unidad = p_unidad_origen;
    
    SELECT factor_conversion, tipo INTO v_factor_destino, v_tipo_destino
    FROM unidad_medida WHERE id_unidad = p_unidad_destino;
    
    IF v_tipo_origen != v_tipo_destino THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se pueden convertir unidades de diferentes tipos';
    END IF;
    
    SET p_resultado = p_cantidad * (v_factor_origen / v_factor_destino);
END$$

DELIMITER ;

COMMIT;