-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-11-2025 a las 01:15:39
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
-- Base de datos: `divino_eter`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `dni` int(9) NOT NULL,
  `nombre` varchar(25) NOT NULL,
  `apellido` varchar(25) NOT NULL,
  `telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `dni`, `nombre`, `apellido`, `telefono`) VALUES
(1, 47433502, 'Antonio', 'Vinazza', '01145687041'),
(2, 474335022, 'Antonio', 'Vinazza', '01145687041'),
(3, 47433501, 'Marco', 'Caputo', '1132410364'),
(4, 47433500, 'Luca', 'Fontan', '1324514586');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `descripcion` tinytext NOT NULL,
  `titulo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `id_cliente`, `descripcion`, `titulo`) VALUES
(3, 3, 'LOL', 'Curso de seduccion de travestis'),
(6, 1, '123', 'Curso de seduccion de travestis'),
(8, 4, 'asfdasf', 'soy luca fontan');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horariodisponible`
--

CREATE TABLE `horariodisponible` (
  `id_horario` int(10) UNSIGNED NOT NULL,
  `dia` enum('sábado','domingo') NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horariodisponible`
--

INSERT INTO `horariodisponible` (`id_horario`, `dia`, `fecha`, `hora_inicio`, `hora_fin`, `disponible`) VALUES
(6, 'sábado', '2025-11-15', '09:00:00', '12:00:00', 0),
(7, 'domingo', '2025-11-16', '09:00:00', '12:00:00', 1),
(8, 'sábado', '2025-11-22', '09:00:00', '12:00:00', 1),
(9, 'domingo', '2025-11-23', '09:00:00', '12:00:00', 1),
(10, 'sábado', '2025-11-29', '09:00:00', '12:00:00', 1),
(11, 'domingo', '2025-11-30', '09:00:00', '12:00:00', 1),
(12, 'sábado', '2025-11-15', '13:00:00', '16:00:00', 1),
(13, 'domingo', '2025-11-16', '13:00:00', '16:00:00', 1),
(14, 'sábado', '2025-11-22', '13:00:00', '16:00:00', 1),
(15, 'domingo', '2025-11-23', '13:00:00', '16:00:00', 1),
(16, 'sábado', '2025-11-29', '13:00:00', '16:00:00', 1),
(17, 'domingo', '2025-11-30', '13:00:00', '16:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lista_espera`
--

CREATE TABLE `lista_espera` (
  `id_lista` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `id_curso` int(10) UNSIGNED DEFAULT NULL,
  `fecha_solicitada` date DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva`
--

CREATE TABLE `reserva` (
  `id_reserva` int(10) UNSIGNED NOT NULL,
  `id_horario` int(10) UNSIGNED NOT NULL,
  `id_curso` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('confirmada','cancelada') DEFAULT 'confirmada',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reserva`
--

INSERT INTO `reserva` (`id_reserva`, `id_horario`, `id_curso`, `id_usuario`, `fecha_reserva`, `hora_inicio`, `hora_fin`, `estado`, `creado_en`) VALUES
(26, 6, 3, 5, '2025-11-15', '09:00:00', '12:00:00', 'confirmada', '2025-11-13 23:32:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `email`, `password_hash`, `nombre`, `apellido`, `telefono`, `creado_en`, `id_cliente`) VALUES
(3, 'toniomurruga@gmail.com', '$2y$10$tmV8yBLWU12F/wNrIzx7vuQb.P3ae2fEf1sQTjqT19vcbmuoNTiV6', 'Antonio', 'Vinazza', '01145687041', '2025-11-09 19:10:24', 1),
(4, 'toniomurruga2@gmail.com', '$2y$10$30XN8I4vRNSTnyoqrYOtH.DlP7DcDzqZYuXOR/IdtgqkRAw/CSE.W', 'Antonio', 'Vinazza', '01145687041', '2025-11-09 19:10:50', 2),
(5, 'marco@gmail.com', '$2y$10$/VOP067EYHsa11xdfX/H7.mXCH8.OOZJF8FR0MJrHNlJ2./pcNN0O', 'Marco', 'Caputo', '1132410364', '2025-11-09 19:52:47', 3),
(6, 'luca@gmail.com', '$2y$10$SIgVg9Iq1tjZzjcruyUxHukj.5gKi.MmZ50CGmiY83i47MqUrCTem', 'Luca', 'Fontan', '1324514586', '2025-11-13 23:53:28', 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `ux_clientes_dni` (`dni`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `horariodisponible`
--
ALTER TABLE `horariodisponible`
  ADD PRIMARY KEY (`id_horario`),
  ADD UNIQUE KEY `fecha` (`fecha`,`hora_inicio`,`hora_fin`);

--
-- Indices de la tabla `lista_espera`
--
ALTER TABLE `lista_espera`
  ADD PRIMARY KEY (`id_lista`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `reserva`
--
ALTER TABLE `reserva`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `id_horario` (`id_horario`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `idx_reserva_fecha` (`fecha_reserva`,`hora_inicio`,`hora_fin`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `horariodisponible`
--
ALTER TABLE `horariodisponible`
  MODIFY `id_horario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `lista_espera`
--
ALTER TABLE `lista_espera`
  MODIFY `id_lista` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reserva`
--
ALTER TABLE `reserva`
  MODIFY `id_reserva` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lista_espera`
--
ALTER TABLE `lista_espera`
  ADD CONSTRAINT `lista_espera_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reserva`
--
ALTER TABLE `reserva`
  ADD CONSTRAINT `reserva_ibfk_1` FOREIGN KEY (`id_horario`) REFERENCES `horariodisponible` (`id_horario`) ON DELETE CASCADE,
  ADD CONSTRAINT `reserva_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
