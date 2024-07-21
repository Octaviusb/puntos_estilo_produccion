-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 09, 2024 at 03:36 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mi_proyecto`
--

-- --------------------------------------------------------

--
-- Table structure for table `beneficios`
--

CREATE TABLE `beneficios` (
  `id` int NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `puntos_necesarios` int DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `canje`
--

CREATE TABLE `canje` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `puntos_canjeados` int NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `canjeado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_canje` date DEFAULT NULL,
  `puntos` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empleados`
--

CREATE TABLE `empleados` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `departamento` varchar(50) NOT NULL,
  `cargo` varchar(50) NOT NULL,
  `puntos` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_canjes`
--

CREATE TABLE `historial_canjes` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `descripcion` varchar(255) DEFAULT NULL,
  `cantidad_puntos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_puntos`
--

CREATE TABLE `historial_puntos` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cantidad` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `puntos`
--

CREATE TABLE `puntos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `puntos` int NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_vencimiento` date NOT NULL DEFAULT ((curdate() + interval 180 day))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reglas_asignacion`
--

CREATE TABLE `reglas_asignacion` (
  `id` int NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `puntos` int NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('admin','usuario') NOT NULL DEFAULT 'usuario',
  `terms` tinyint(1) NOT NULL DEFAULT '0',
  `otp` tinyint(1) NOT NULL DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `celular` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contraseña`, `rol`, `terms`, `otp`, `creado_en`, `celular`, `fecha_nacimiento`) VALUES
(1, 'Ener Octavio Buitrago', 'obuitragocamelo@yahoo.es', '$2y$10$DnlQvduWTXaWrhCNoCd0fOHH2ZCKdZ5plzZgeQ3HjKXHev6c/.WeG', 'admin', 0, 0, '2024-06-14 19:52:16', NULL, NULL),
(2, 'Octavio Buitrago', 'ladriecol@gmail.com', '$2y$10$QMHza53eaRUmJFK7TmaAm.dsYfe7dLqRqE.rMiIcNXwSJpWZGtpbW', 'usuario', 0, 0, '2024-06-19 15:24:28', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `beneficios`
--
ALTER TABLE `beneficios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `canje`
--
ALTER TABLE `canje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indexes for table `historial_canjes`
--
ALTER TABLE `historial_canjes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `historial_puntos`
--
ALTER TABLE `historial_puntos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `puntos`
--
ALTER TABLE `puntos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `reglas_asignacion`
--
ALTER TABLE `reglas_asignacion`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `beneficios`
--
ALTER TABLE `beneficios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `canje`
--
ALTER TABLE `canje`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_canjes`
--
ALTER TABLE `historial_canjes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_puntos`
--
ALTER TABLE `historial_puntos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `puntos`
--
ALTER TABLE `puntos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reglas_asignacion`
--
ALTER TABLE `reglas_asignacion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `canje`
--
ALTER TABLE `canje`
  ADD CONSTRAINT `canje_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historial_canjes`
--
ALTER TABLE `historial_canjes`
  ADD CONSTRAINT `historial_canjes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `historial_puntos`
--
ALTER TABLE `historial_puntos`
  ADD CONSTRAINT `historial_puntos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `puntos`
--
ALTER TABLE `puntos`
  ADD CONSTRAINT `puntos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
