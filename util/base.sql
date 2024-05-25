-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-05-2024 a las 22:38:40
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
-- Base de datos: `base`
--
CREATE DATABASE IF NOT EXISTS `base` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `base`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `evento` varchar(250) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

CREATE TABLE `puestos` (
  `id` bigint(20) NOT NULL,
  `puesto` varchar(250) NOT NULL,
  `depende` bigint(20) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `puestos`
--

INSERT INTO `puestos` (`id`, `puesto`, `depende`, `fecha`) VALUES
(1, 'Gran Institución', 1, '2012-01-14 19:13:16'),
(2, 'Gerente General', 1, '2012-01-14 19:21:42'),
(3, 'Gerente Administrativo', 2, '2012-02-26 21:47:35'),
(4, 'Gerente de Ventas', 2, '2012-02-26 21:47:35'),
(5, 'Recursos Humanos', 3, '2012-02-26 21:48:06'),
(6, 'Mercadotecnia', 4, '2012-02-26 21:48:06'),
(7, 'Almacén', 3, '2012-12-30 20:20:08'),
(8, 'Adquisiciones', 3, '2012-12-30 20:20:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `contraseña` char(40) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `contraseña`, `fecha`) VALUES
(1, 'Galileo', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '2024-02-25 20:00:00'),
(2, 'Alicia', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '2024-02-25 20:00:00'),
(3, 'Nabih', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '2024-02-25 20:00:00'),
(4, 'Soledad', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '2024-02-25 20:00:00'),
(5, 'Isra', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '2024-02-25 20:00:00'),
(6, 'Oscar', '5f6955d227a320c7f1f6c7da2a6d96a851a8118f', '2024-02-25 20:00:00');

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `nuevo_usuario` AFTER INSERT ON `usuarios` FOR EACH ROW INSERT INTO historial (evento) values ( concat('Se insertó un nuevo usuario con ID: ', NEW.id, ' con el nombre: ', NEW.nombre) )
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_puestos`
--

CREATE TABLE `usuarios_puestos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` bigint(11) NOT NULL,
  `puesto` bigint(11) NOT NULL,
  `inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuarios_puestos`
--

INSERT INTO `usuarios_puestos` (`id`, `usuario`, `puesto`, `inicio`, `fin`) VALUES
(1, 1, 2, '2012-02-26 21:38:46', NULL),
(4, 2, 3, '2012-02-26 22:23:31', NULL),
(5, 3, 4, '2012-02-26 22:23:31', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `puestos`
--
ALTER TABLE `puestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `depende` (`depende`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios_puestos`
--
ALTER TABLE `usuarios_puestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `puesto` (`puesto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `puestos`
--
ALTER TABLE `puestos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT de la tabla `usuarios_puestos`
--
ALTER TABLE `usuarios_puestos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `puestos`
--
ALTER TABLE `puestos`
  ADD CONSTRAINT `puestos_ibfk_1` FOREIGN KEY (`depende`) REFERENCES `puestos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios_puestos`
--
ALTER TABLE `usuarios_puestos`
  ADD CONSTRAINT `usuarios_puestos_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_puestos_ibfk_2` FOREIGN KEY (`puesto`) REFERENCES `puestos` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
