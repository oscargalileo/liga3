-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 30-12-2012 a las 14:35:23
-- Versión del servidor: 5.5.16-log
-- Versión de PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `base`
--
CREATE DATABASE `base` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `base`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE IF NOT EXISTS `historial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento` varchar(250) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

CREATE TABLE IF NOT EXISTS `puestos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `puesto` varchar(250) NOT NULL,
  `depende` bigint(20) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `depende` (`depende`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Volcado de datos para la tabla `puestos`
--

INSERT INTO `puestos` (`id`, `puesto`, `depende`, `fecha`) VALUES
(1, 'Gran Institución', 1, '2012-01-14 19:13:16'),
(2, 'Gerente General', 1, '2012-01-14 19:21:42'),
(3, 'Gerente Administrativo', 2, '2012-02-26 21:47:35'),
(4, 'Gerente de Ventas', 2, '2012-02-26 21:47:35'),
(5, 'Recursos Humanos', 3, '2012-02-26 21:48:06'),
(6, 'Almacén', 3, '2012-12-30 20:20:08'),
(7, 'Adquisiciones', 3, '2012-12-30 20:20:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `contraseña` char(32) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `contraseña`, `fecha`) VALUES
(1, 'Galileo', '202cb962ac59075b964b07152d234b70', '2025-01-23 17:00:00'),
(2, 'Alicia', '202cb962ac59075b964b07152d234b70', '2025-01-23 17:00:00'),
(3, 'Nabih', '202cb962ac59075b964b07152d234b70', '2025-01-23 17:00:00');

--
-- Disparadores `usuarios`
--
DROP TRIGGER IF EXISTS `nuevo_usuario`;
DELIMITER //
CREATE TRIGGER `nuevo_usuario` AFTER INSERT ON `usuarios`
 FOR EACH ROW INSERT INTO historial (evento) values ( concat('Se insertó un nuevo usuario con ID: ', NEW.id, ' con el nombre: ', NEW.nombre) )
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_puestos`
--

CREATE TABLE IF NOT EXISTS `usuarios_puestos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario` bigint(11) NOT NULL,
  `puesto` bigint(11) NOT NULL,
  `inicio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario` (`usuario`),
  KEY `puesto` (`puesto`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `usuarios_puestos`
--

INSERT INTO `usuarios_puestos` (`id`, `usuario`, `puesto`, `inicio`, `fin`) VALUES
(1, 1, 2, '2012-02-26 21:38:46', NULL),
(2, 2, 3, '2012-02-26 22:23:31', NULL),
(3, 3, 4, '2012-02-26 22:23:31', NULL);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
