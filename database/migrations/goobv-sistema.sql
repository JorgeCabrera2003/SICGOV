-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-01-2026 a las 00:17:20
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `goobv-sistema`
--
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `cedula_personal` varchar(12) NOT NULL,
  `nombres` varchar(65) NOT NULL,
  `apellidos` varchar(65) NOT NULL,
  `id_cargo` varchar(24) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `salario` decimal(10,2) DEFAULT 0.00,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` varchar(24) NOT NULL,
  `cedula_personal` varchar(12) NOT NULL,
  `tipo_marcacion` enum('ENTRADA','SALIDA','DESCANSO') NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `observacion` varchar(100) DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` varchar(24) NOT NULL,
  `nombre_cargo` varchar(45) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`id_cargo`, `nombre_cargo`, `descripcion`, `estatus`) VALUES
('COCIN0012025100112011321', 'Chef Principal', 'Encargado de la cocina y creación de platillos', 1),
('GEREN0022025100112011321', 'Gerente', 'Administración del restaurante', 1),
('MESER0032025100112011321', 'Mesero', 'Atención al cliente y servicio en mesa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_producto`
--

CREATE TABLE `categoria_producto` (
  `id_categoria` varchar(24) NOT NULL,
  `nombre_categoria` varchar(45) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_producto`
--

INSERT INTO `categoria_producto` (`id_categoria`, `nombre_categoria`, `descripcion`, `icono`, `estatus`) VALUES
('BEBID0012025100923103488', 'Bebidas Retro', 'Bebidas icónicas de los 80s y 90s', 'drink.png', 1),
('COMID0012025100923103181', 'Comida Rápida', 'Hamburguesas, papas fritas, hot dogs', 'fastfood.png', 1),
('POSTR0012025100923103182', 'Postres', 'Helados, tortas, brownies', 'dessert.png', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` varchar(24) NOT NULL,
  `id_categoria` varchar(24) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 0,
  `costo_preparacion` decimal(10,2) DEFAULT NULL,
  `tiempo_preparacion` int(11) DEFAULT NULL COMMENT 'En minutos',
  `imagen` varchar(100) DEFAULT NULL,
  `es_personalizable` tinyint(1) DEFAULT 0,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingrediente`
--

CREATE TABLE `ingrediente` (
  `id_ingrediente` varchar(24) NOT NULL,
  `nombre_ingrediente` varchar(100) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `receta`
--

CREATE TABLE `receta` (
  `id_receta` varchar(24) NOT NULL,
  `id_producto` varchar(24) NOT NULL,
  `id_ingrediente` varchar(24) NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `instrucciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_inventario` varchar(24) NOT NULL,
  `id_ingrediente` varchar(24) NOT NULL,
  `cantidad_actual` decimal(10,3) NOT NULL,
  `stock_minimo` decimal(10,3) NOT NULL,
  `stock_maximo` decimal(10,3) NOT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_inventario`
--

CREATE TABLE `movimiento_inventario` (
  `id_movimiento` varchar(24) NOT NULL,
  `id_ingrediente` varchar(24) NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','AJUSTE') NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `responsable` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesa`
--

CREATE TABLE `mesa` (
  `id_mesa` varchar(24) NOT NULL,
  `numero_mesa` int(11) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `qr_code` varchar(100) DEFAULT NULL,
  `estado` enum('DISPONIBLE','OCUPADA','RESERVADA','MANTENIMIENTO') NOT NULL DEFAULT 'DISPONIBLE',
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` varchar(24) NOT NULL,
  `nombres` varchar(65) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` varchar(24) NOT NULL,
  `id_mesa` varchar(24) DEFAULT NULL,
  `id_cliente` varchar(24) DEFAULT NULL,
  `cedula_mesero` varchar(12) DEFAULT NULL,
  `tipo_pedido` enum('MESA','DOMICILIO','RECOGIDA') NOT NULL,
  `estado_pedido` enum('PENDIENTE','EN_PREPARACION','LISTO','ENTREGADO','CANCELADO') NOT NULL DEFAULT 'PENDIENTE',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` varchar(24) NOT NULL,
  `id_pedido` varchar(24) NOT NULL,
  `id_producto` varchar(24) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `personalizaciones` text DEFAULT NULL,
  `instrucciones_especiales` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id_pago` varchar(24) NOT NULL,
  `id_pedido` varchar(24) NOT NULL,
  `metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','PUNTO_VENTA') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `vuelto` decimal(10,2) DEFAULT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `responsable` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion_financiera`
--

CREATE TABLE `transaccion_financiera` (
  `id_transaccion` varchar(24) NOT NULL,
  `tipo_transaccion` enum('INGRESO','EGRESO') NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `fecha_transaccion` date NOT NULL,
  `responsable` varchar(12) DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id_config` varchar(24) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id_config`, `clave`, `valor`, `descripcion`, `categoria`, `estatus`) VALUES
('CONFIG00120251001', 'IVA_PORCENTAJE', '16', 'Porcentaje de IVA aplicable', 'FINANZAS', 1),
('CONFIG00220251001', 'MODO_RETRO', '1', 'Activar diseño retro 80s/90s', 'APARIENCIA', 1),
('CONFIG00320251001', 'ALERTA_STOCK_MINIMO', '1', 'Activar alertas de stock mínimo', 'INVENTARIO', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promocion`
--

CREATE TABLE `promocion` (
  `id_promocion` varchar(24) NOT NULL,
  `nombre_promocion` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_descuento` enum('PORCENTAJE','MONTO_FIJO') NOT NULL,
  `valor_descuento` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turno`
--

CREATE TABLE `turno` (
  `id_turno` varchar(24) NOT NULL,
  `nombre_turno` varchar(50) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `turno`
--

INSERT INTO `turno` (`id_turno`, `nombre_turno`, `hora_inicio`, `hora_fin`, `estatus`) VALUES
('TURNO00120251001', 'Mañana', '08:00:00', '16:00:00', 1),
('TURNO00220251001', 'Tarde', '16:00:00', '00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_inventario_alerta`
--
CREATE TABLE `vista_inventario_alerta` (
`id_ingrediente` varchar(24)
,`nombre_ingrediente` varchar(100)
,`cantidad_actual` decimal(10,3)
,`stock_minimo` decimal(10,3)
,`diferencia` decimal(10,3)
,`necesita_reposicion` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ventas_diarias`
--
CREATE TABLE `vista_ventas_diarias` (
`fecha` date
,`total_ventas` decimal(32,2)
,`cantidad_pedidos` bigint(21)
,`promedio_ticket` decimal(33,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_productos_mas_vendidos`
--
CREATE TABLE `vista_productos_mas_vendidos` (
`id_producto` varchar(24)
,`nombre_producto` varchar(100)
,`categoria` varchar(45)
,`cantidad_vendida` decimal(32,0)
,`total_recaudado` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_asistencia_personal`
--
CREATE TABLE `vista_asistencia_personal` (
`cedula_personal` varchar(12)
,`nombres_completos` varchar(131)
,`cargo` varchar(45)
,`dias_trabajados` bigint(21)
,`ultima_asistencia` date
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_inventario_alerta`
--
DROP TABLE IF EXISTS `vista_inventario_alerta`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_inventario_alerta`  AS SELECT `i`.`id_ingrediente` AS `id_ingrediente`, `ing`.`nombre_ingrediente` AS `nombre_ingrediente`, `i`.`cantidad_actual` AS `cantidad_actual`, `i`.`stock_minimo` AS `stock_minimo`, (`i`.`cantidad_actual` - `i`.`stock_minimo`) AS `diferencia`, CASE WHEN `i`.`cantidad_actual` <= `i`.`stock_minimo` THEN 1 ELSE 0 END AS `necesita_reposicion` FROM (`inventario` `i` join `ingrediente` `ing` on(`i`.`id_ingrediente` = `ing`.`id_ingrediente`)) WHERE `i`.`estatus` = 1 AND `ing`.`estatus` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ventas_diarias`
--
DROP TABLE IF EXISTS `vista_ventas_diarias`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_ventas_diarias`  AS SELECT cast(`p`.`fecha_pedido` as date) AS `fecha`, sum(`p`.`total`) AS `total_ventas`, count(`p`.`id_pedido`) AS `cantidad_pedidos`, avg(`p`.`total`) AS `promedio_ticket` FROM `pedido` `p` WHERE `p`.`estatus` = 1 AND `p`.`estado_pedido` = 'ENTREGADO' GROUP BY cast(`p`.`fecha_pedido` as date) ORDER BY cast(`p`.`fecha_pedido` as date) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_productos_mas_vendidos`
--
DROP TABLE IF EXISTS `vista_productos_mas_vendidos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_productos_mas_vendidos`  AS SELECT `dp`.`id_producto` AS `id_producto`, `pr`.`nombre_producto` AS `nombre_producto`, `cp`.`nombre_categoria` AS `categoria`, sum(`dp`.`cantidad`) AS `cantidad_vendida`, sum(`dp`.`subtotal`) AS `total_recaudado` FROM ((`detalle_pedido` `dp` join `pedido` `p` on(`dp`.`id_pedido` = `p`.`id_pedido`)) join `producto` `pr` on(`dp`.`id_producto` = `pr`.`id_producto`)) join `categoria_producto` `cp` on(`pr`.`id_categoria` = `cp`.`id_categoria`) WHERE `p`.`estatus` = 1 AND `p`.`estado_pedido` = 'ENTREGADO' GROUP BY `dp`.`id_producto`, `pr`.`nombre_producto`, `cp`.`nombre_categoria` ORDER BY sum(`dp`.`cantidad`) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_asistencia_personal`
--
DROP TABLE IF EXISTS `vista_asistencia_personal`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_asistencia_personal`  AS SELECT `p`.`cedula_personal` AS `cedula_personal`, concat(`p`.`nombres`,' ',`p`.`apellidos`) AS `nombres_completos`, `c`.`nombre_cargo` AS `cargo`, count(distinct `a`.`fecha`) AS `dias_trabajados`, max(`a`.`fecha`) AS `ultima_asistencia` FROM ((`personal` `p` left join `asistencia` `a` on(`p`.`cedula_personal` = `a`.`cedula_personal`)) left join `cargo` `c` on(`p`.`id_cargo` = `c`.`id_cargo`)) WHERE `p`.`estatus` = 1 AND (`a`.`estatus` = 1 OR `a`.`estatus` IS NULL) GROUP BY `p`.`cedula_personal`, `p`.`nombres`, `p`.`apellidos`, `c`.`nombre_cargo` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`cedula_personal`),
  ADD KEY `id_cargo` (`id_cargo`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `cedula_personal` (`cedula_personal`),
  ADD KEY `fecha` (`fecha`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`),
  ADD UNIQUE KEY `nombre_cargo` (`nombre_cargo`);

--
-- Indices de la tabla `categoria_producto`
--
ALTER TABLE `categoria_producto`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `nombre_producto` (`nombre_producto`);

--
-- Indices de la tabla `ingrediente`
--
ALTER TABLE `ingrediente`
  ADD PRIMARY KEY (`id_ingrediente`),
  ADD UNIQUE KEY `nombre_ingrediente` (`nombre_ingrediente`);

--
-- Indices de la tabla `receta`
--
ALTER TABLE `receta`
  ADD PRIMARY KEY (`id_receta`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_ingrediente` (`id_ingrediente`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_inventario`),
  ADD UNIQUE KEY `id_ingrediente` (`id_ingrediente`),
  ADD KEY `idx_stock_minimo` (`cantidad_actual`,`stock_minimo`);

--
-- Indices de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_ingrediente` (`id_ingrediente`),
  ADD KEY `fecha_movimiento` (`fecha_movimiento`),
  ADD KEY `responsable` (`responsable`);

--
-- Indices de la tabla `mesa`
--
ALTER TABLE `mesa`
  ADD PRIMARY KEY (`id_mesa`),
  ADD UNIQUE KEY `numero_mesa` (`numero_mesa`),
  ADD UNIQUE KEY `qr_code` (`qr_code`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `telefono` (`telefono`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_mesa` (`id_mesa`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `cedula_mesero` (`cedula_mesero`),
  ADD KEY `fecha_pedido` (`fecha_pedido`),
  ADD KEY `estado_pedido` (`estado_pedido`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`),
  ADD KEY `fecha_pago` (`fecha_pago`),
  ADD KEY `responsable` (`responsable`);

--
-- Indices de la tabla `transaccion_financiera`
--
ALTER TABLE `transaccion_financiera`
  ADD PRIMARY KEY (`id_transaccion`),
  ADD KEY `fecha_transaccion` (`fecha_transaccion`),
  ADD KEY `tipo_transaccion` (`tipo_transaccion`),
  ADD KEY `responsable` (`responsable`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `clave` (`clave`),
  ADD KEY `categoria` (`categoria`);

--
-- Indices de la tabla `promocion`
--
ALTER TABLE `promocion`
  ADD PRIMARY KEY (`id_promocion`),
  ADD KEY `fecha_inicio` (`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `turno`
--
ALTER TABLE `turno`
  ADD PRIMARY KEY (`id_turno`),
  ADD UNIQUE KEY `nombre_turno` (`nombre_turno`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`cedula_personal`) REFERENCES `personal` (`cedula_personal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_producto` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `receta`
--
ALTER TABLE `receta`
  ADD CONSTRAINT `receta_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `receta_ibfk_2` FOREIGN KEY (`id_ingrediente`) REFERENCES `ingrediente` (`id_ingrediente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`id_ingrediente`) REFERENCES `ingrediente` (`id_ingrediente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `movimiento_inventario_ibfk_1` FOREIGN KEY (`id_ingrediente`) REFERENCES `ingrediente` (`id_ingrediente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `movimiento_inventario_ibfk_2` FOREIGN KEY (`responsable`) REFERENCES `personal` (`cedula_personal`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesa` (`id_mesa`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pedido_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pedido_ibfk_3` FOREIGN KEY (`cedula_mesero`) REFERENCES `personal` (`cedula_personal`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pago_ibfk_2` FOREIGN KEY (`responsable`) REFERENCES `personal` (`cedula_personal`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `transaccion_financiera`
--
ALTER TABLE `transaccion_financiera`
  ADD CONSTRAINT `transaccion_financiera_ibfk_1` FOREIGN KEY (`responsable`) REFERENCES `personal` (`cedula_personal`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;