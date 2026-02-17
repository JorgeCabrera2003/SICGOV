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
-- Base de datos: `goobv-usuarios`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` varchar(24) NOT NULL,
  `nombre_rol` varchar(45) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`, `estatus`) VALUES
('ADMIN00120251001', 'ADMINISTRADOR', 'Acceso completo al sistema', 1),
('CAJA00220251001', 'CAJERO', 'Manejo de pagos y caja', 1),
('CHEF00320251001', 'CHEF', 'Gestión de cocina y recetas', 1),
('CLIE00420251001', 'CLIENTE', 'Acceso a menú interactivo y pedidos', 1),
('GEREN00520251001', 'GERENTE', 'Supervisión y reportes', 1),
('MESER00620251001', 'MESERO', 'Toma de pedidos y atención', 1),
('SUPER00720251001', 'SUPERUSUARIO', 'Desarrollo y configuración', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` varchar(24) NOT NULL,
  `cedula` varchar(12) NOT NULL,
  `id_rol` varchar(24) NOT NULL,
  `username` varchar(45) NOT NULL,
  `nombres` varchar(65) NOT NULL,
  `apellidos` varchar(65) NOT NULL,
  `telefono` varchar(13) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `foto_perfil` varchar(100) DEFAULT NULL,
  `tema_oscuro` tinyint(1) DEFAULT 0,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

CREATE TABLE `modulo` (
  `id_modulo` varchar(24) NOT NULL,
  `nombre_modulo` varchar(45) NOT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `ruta` varchar(100) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulo`
--

INSERT INTO `modulo` (`id_modulo`, `nombre_modulo`, `icono`, `ruta`, `orden`, `estatus`) VALUES
('ASIST00120251001', 'Asistencia', 'clock', '/asistencia', 5, 1),
('CLIEN00920251001', 'Clientes', 'users', '/clientes', 8, 1),
('COCIN00620251001', 'Cocina', 'utensils', '/cocina', 6, 1),
('FINAN00420251001', 'Finanzas', 'dollar-sign', '/finanzas', 4, 1),
('INVEN00320251001', 'Inventario', 'package', '/inventario', 3, 1),
('MENU00820251001', 'Menú', 'book-open', '/menu', 8, 1),
('PERSO00220251001', 'Personal', 'user', '/personal', 2, 1),
('PROMO01020251001', 'Promociones', 'tag', '/promociones', 10, 1),
('REPOR00520251001', 'Reportes', 'bar-chart', '/reportes', 5, 1),
('SUPER00720251001', 'Supervisión', 'eye', '/supervision', 7, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `id_permiso` varchar(24) NOT NULL,
  `id_rol` varchar(24) NOT NULL,
  `id_modulo` varchar(24) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion`
--

CREATE TABLE `sesion` (
  `id_sesion` varchar(24) NOT NULL,
  `id_usuario` varchar(24) NOT NULL,
  `token` varchar(255) NOT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` varchar(24) NOT NULL,
  `id_usuario` varchar(24) DEFAULT NULL,
  `modulo` varchar(45) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` varchar(24) NOT NULL,
  `id_usuario` varchar(24) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `tipo` enum('INFO','ALERTA','EXITO','ERROR') NOT NULL DEFAULT 'INFO',
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_permisos_usuario`
--
CREATE TABLE `vista_permisos_usuario` (
`id_usuario` varchar(24)
,`username` varchar(45)
,`nombre_rol` varchar(45)
,`modulos_permitidos` text
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_actividad_usuarios`
--
CREATE TABLE `vista_actividad_usuarios` (
`id_usuario` varchar(24)
,`username` varchar(45)
,`nombres_completos` varchar(131)
,`nombre_rol` varchar(45)
,`ultimo_acceso` timestamp
,`sesiones_activas` bigint(21)
,`acciones_hoy` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_permisos_usuario`
--
DROP TABLE IF EXISTS `vista_permisos_usuario`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_permisos_usuario`  AS SELECT `u`.`id_usuario` AS `id_usuario`, `u`.`username` AS `username`, `r`.`nombre_rol` AS `nombre_rol`, group_concat(distinct `m`.`nombre_modulo` order by `m`.`orden` separator ', ') AS `modulos_permitidos` FROM (((`usuario` `u` join `rol` `r` on(`u`.`id_rol` = `r`.`id_rol`)) join `permiso` `p` on(`r`.`id_rol` = `p`.`id_rol`)) join `modulo` `m` on(`p`.`id_modulo` = `m`.`id_modulo`)) WHERE `u`.`estatus` = 1 AND `r`.`estatus` = 1 AND `m`.`estatus` = 1 AND `p`.`estado` = 1 GROUP BY `u`.`id_usuario`, `u`.`username`, `r`.`nombre_rol` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_actividad_usuarios`
--
DROP TABLE IF EXISTS `vista_actividad_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_actividad_usuarios`  AS SELECT `u`.`id_usuario` AS `id_usuario`, `u`.`username` AS `username`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `nombres_completos`, `r`.`nombre_rol` AS `nombre_rol`, `u`.`ultimo_acceso` AS `ultimo_acceso`, count(`s`.`id_sesion`) AS `sesiones_activas`, (select count(0) from `bitacora` `b` where `b`.`id_usuario` = `u`.`id_usuario` and cast(`b`.`fecha` as date) = curdate()) AS `acciones_hoy` FROM ((`usuario` `u` join `rol` `r` on(`u`.`id_rol` = `r`.`id_rol`)) left join `sesion` `s` on(`u`.`id_usuario` = `s`.`id_usuario` and `s`.`activa` = 1)) WHERE `u`.`estatus` = 1 GROUP BY `u`.`id_usuario`, `u`.`username`, `u`.`nombres`, `u`.`apellidos`, `r`.`nombre_rol`, `u`.`ultimo_acceso` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`id_modulo`),
  ADD UNIQUE KEY `nombre_modulo` (`nombre_modulo`);

--
-- Indices de la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `unique_permiso` (`id_rol`,`id_modulo`,`accion`),
  ADD KEY `id_modulo` (`id_modulo`);

--
-- Indices de la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD PRIMARY KEY (`id_sesion`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fecha_expiracion` (`fecha_expiracion`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fecha` (`fecha`),
  ADD KEY `modulo` (`modulo`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `leida` (`leida`),
  ADD KEY `fecha` (`fecha`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD CONSTRAINT `permiso_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permiso_ibfk_2` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD CONSTRAINT `sesion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;