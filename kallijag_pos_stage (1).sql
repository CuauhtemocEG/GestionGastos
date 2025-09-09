-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 08-09-2025 a las 20:45:29
-- Versión del servidor: 5.7.23-23
-- Versión de PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `kallijag_pos_stage`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `descripcion`, `creado_en`, `actualizado_en`) VALUES
(1, 'app_name', 'GastosApp', 'Nombre de la aplicación', '2025-09-09 02:25:47', '2025-09-09 02:25:47'),
(2, 'app_version', '2.0', 'Versión de la aplicación', '2025-09-09 02:25:47', '2025-09-09 02:25:47'),
(3, 'moneda_default', 'MXN', 'Moneda por defecto', '2025-09-09 02:25:47', '2025-09-09 02:25:47'),
(4, 'simbolo_moneda', '$', 'Símbolo de la moneda', '2025-09-09 02:25:47', '2025-09-09 02:25:47'),
(5, 'fecha_backup', NULL, 'Última fecha de backup', '2025-09-09 02:25:47', '2025-09-09 02:25:47'),
(6, 'session_timeout', '3600', 'Tiempo de sesión en segundos', '2025-09-09 02:25:47', '2025-09-09 02:25:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Gastos`
--

CREATE TABLE `Gastos` (
  `ID` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `Descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Monto` decimal(10,2) NOT NULL,
  `Metodo` enum('Tarjeta','Efectivo') COLLATE utf8_unicode_ci NOT NULL,
  `Tipo` enum('Fijo','Central','Mercado','Mantenimiento','Inversiones') COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Gastos`
--

INSERT INTO `Gastos` (`ID`, `Fecha`, `Descripcion`, `Monto`, `Metodo`, `Tipo`) VALUES
(9, '2025-06-12', 'GASOLINA MAU ', 500.00, 'Efectivo', 'Fijo'),
(11, '2025-06-13', 'UBER', 51.00, 'Efectivo', 'Mantenimiento'),
(12, '2025-06-16', 'CHICHARRON ', 1360.00, 'Efectivo', 'Central'),
(13, '2025-06-16', 'NARANJA ', 1140.00, 'Efectivo', 'Central'),
(14, '2025-06-16', 'LONA', 200.00, 'Efectivo', 'Inversiones'),
(15, '2025-06-16', 'VICTOR', 29.00, 'Efectivo', 'Central'),
(16, '2025-06-16', 'GASOLINA', 500.00, 'Efectivo', 'Fijo'),
(17, '2025-06-16', 'CARGADORES CENTRAL', 120.00, 'Efectivo', 'Central'),
(18, '2025-06-17', 'MOLINO Y GALLETAS ', 760.00, 'Efectivo', 'Central'),
(19, '2025-06-17', 'REQUESON', 200.00, 'Efectivo', 'Central'),
(20, '2025-06-23', 'CHICHARRON ', 1800.00, 'Efectivo', 'Central'),
(21, '2025-06-26', 'CENTRAL', 8323.00, 'Efectivo', 'Central'),
(22, '2025-06-26', 'MERCADO ', 3540.00, 'Efectivo', 'Central'),
(23, '2025-06-30', 'GASOLINA', 500.00, 'Efectivo', 'Fijo'),
(24, '2025-06-30', 'POBLANO Y CALABAZA', 77.00, 'Efectivo', 'Central'),
(25, '2025-06-30', 'MOLINO CHILE ', 300.00, 'Efectivo', 'Inversiones'),
(26, '2025-07-01', 'PAGO ALFONSO ', 1500.00, 'Efectivo', 'Fijo'),
(27, '2025-06-25', 'CREMERIA ', 5185.00, 'Tarjeta', 'Central'),
(28, '2025-06-24', 'TOSTADAS', 1442.00, 'Tarjeta', 'Inversiones'),
(29, '2025-06-24', 'AGUA', 527.00, 'Tarjeta', 'Inversiones'),
(30, '2025-06-23', 'SAMS (chipotle,clavel,roma y jugo)', 2363.00, 'Tarjeta', 'Inversiones'),
(31, '2025-06-23', 'COFFEE MATE', 1090.00, 'Tarjeta', 'Inversiones'),
(32, '2025-06-23', 'WALTMART (yoli, nesquick y morelia)', 3685.00, 'Tarjeta', 'Inversiones'),
(33, '2025-06-26', 'CHALUPA', 750.00, 'Tarjeta', 'Inversiones'),
(34, '2025-06-26', 'COMISION', 208.00, 'Tarjeta', 'Inversiones'),
(35, '2025-06-26', 'AGUA', 279.00, 'Tarjeta', 'Inversiones'),
(36, '2025-07-01', 'SECOS', 4398.00, 'Tarjeta', 'Inversiones'),
(37, '2025-06-30', 'ARERO MAYOREO', 5280.00, 'Tarjeta', 'Inversiones'),
(38, '2025-06-30', 'ARERO MENUDEO ', 1341.00, 'Tarjeta', 'Inversiones'),
(39, '2025-07-01', 'PASTAS Y CHOCOLATE ', 8290.00, 'Tarjeta', 'Inversiones'),
(40, '2025-06-30', 'COSTCO (leche,jarabe, escoba,cafe)', 5019.00, 'Tarjeta', 'Inversiones'),
(41, '2025-06-30', 'TOSTADAS', 1610.00, 'Tarjeta', 'Inversiones'),
(42, '2025-07-01', 'AGUA', 651.00, 'Tarjeta', 'Inversiones'),
(44, '2025-07-03', 'MERCADO ', 4437.00, 'Efectivo', 'Central'),
(45, '2025-07-03', 'MERCADO ', 4962.00, 'Tarjeta', 'Central'),
(46, '2025-07-03', 'CENTRAL', 1217.00, 'Tarjeta', 'Central'),
(47, '2025-07-04', 'CENTRAL', 5061.00, 'Efectivo', 'Central'),
(49, '2025-07-04', 'GASOLINA Y MANIOBRAS ', 600.00, 'Efectivo', 'Fijo'),
(50, '2025-07-10', 'CENTRAL', 4158.00, 'Efectivo', 'Central'),
(51, '2025-07-10', 'MERCADO ', 7993.00, 'Efectivo', 'Mercado'),
(52, '2025-07-14', 'CACAO', 2230.00, 'Efectivo', 'Central'),
(53, '2025-07-11', 'UBER GARRAFONES ', 134.00, 'Efectivo', 'Inversiones'),
(54, '2025-07-11', 'UBER GARRAFONES ', 57.00, 'Efectivo', 'Inversiones'),
(55, '2025-07-15', 'AGUA ', 620.00, 'Tarjeta', 'Inversiones'),
(56, '2025-07-12', 'WALTMART (yoli aceite y chocolate)', 1972.00, 'Tarjeta', 'Inversiones'),
(57, '2025-07-15', 'TOSTADAS', 1610.00, 'Tarjeta', 'Inversiones'),
(58, '2025-07-16', 'CENTRAL', 3000.00, 'Tarjeta', 'Central'),
(59, '2025-07-16', 'SUELDO ADMINISTRACION', 4500.00, 'Tarjeta', 'Fijo'),
(60, '2025-07-16', 'GASOLINA', 1000.00, 'Tarjeta', 'Fijo'),
(61, '2025-07-16', 'RENTA', 5000.00, 'Tarjeta', 'Fijo'),
(63, '2025-07-16', 'GAS NATURAL', 509.00, 'Tarjeta', 'Fijo'),
(65, '2025-07-17', 'CENTRAL', 4200.00, 'Efectivo', 'Central'),
(66, '2025-07-17', 'COMISION', 510.00, 'Tarjeta', 'Inversiones'),
(67, '2025-07-16', 'LUZ', 3000.00, 'Tarjeta', 'Fijo'),
(68, '2025-07-17', 'CHEDRAUI (aguacate, galletas)', 743.74, 'Tarjeta', 'Inversiones'),
(69, '2025-07-18', 'AGUA ', 155.00, 'Tarjeta', 'Inversiones'),
(70, '2025-07-18', 'MERCADO ', 6885.50, 'Efectivo', 'Mercado'),
(71, '2025-07-18', 'MOLINO', 700.00, 'Efectivo', 'Inversiones'),
(72, '2025-07-24', 'GASOLINA', 500.00, 'Efectivo', 'Fijo'),
(73, '2025-07-23', 'CHOCOLATE (doña Oly)', 550.00, 'Tarjeta', 'Inversiones'),
(75, '2025-07-23', 'MERCADO ', 6000.00, 'Tarjeta', 'Mercado'),
(77, '2025-07-23', 'COMISION', 276.50, 'Tarjeta', 'Inversiones'),
(78, '2025-07-22', 'JABON COSTCO', 232.00, 'Tarjeta', 'Inversiones'),
(79, '2025-07-22', 'QUIMICOS ', 649.00, 'Tarjeta', 'Inversiones'),
(80, '2025-07-22', 'CHALUPA', 1500.00, 'Tarjeta', 'Inversiones'),
(81, '2025-07-24', 'SAMS (filtros,desinfectantes,jabon etc)', 1736.00, 'Tarjeta', 'Inversiones'),
(82, '2025-07-23', 'SEMILLAS (Doña Oly)', 1690.00, 'Tarjeta', 'Inversiones'),
(83, '2025-07-24', 'MERCADO', 1492.42, 'Efectivo', 'Mercado'),
(84, '2025-07-24', 'CENTRAL ', 5047.00, 'Efectivo', 'Central'),
(85, '2025-07-28', 'PAGO RESTANTE MICKY ', 589.00, 'Efectivo', 'Inversiones'),
(86, '2025-07-28', 'ZANAHORIA ', 110.00, 'Efectivo', 'Central'),
(87, '2025-07-28', 'LECHUGAS ', 225.00, 'Efectivo', 'Central'),
(88, '2025-07-28', 'FLETE CHALUPA ', 200.00, 'Efectivo', 'Inversiones'),
(89, '2025-07-28', 'CHICHARRON', 2000.00, 'Efectivo', 'Inversiones'),
(90, '2025-07-29', 'ARERO', 6881.28, 'Tarjeta', 'Inversiones'),
(91, '2025-07-30', 'SECOS', 4185.10, 'Tarjeta', 'Central'),
(92, '2025-07-31', 'SUELDO ANDREA', 4500.00, 'Efectivo', 'Fijo'),
(93, '2025-07-29', 'AGUA ', 434.00, 'Tarjeta', 'Inversiones'),
(95, '2025-07-31', 'MERCADO', 8903.00, 'Efectivo', 'Mercado'),
(96, '2025-07-31', 'CENTRAL ', 4697.00, 'Efectivo', 'Central'),
(98, '2025-07-30', 'CHALUPA', 750.00, 'Tarjeta', 'Inversiones'),
(99, '2025-07-31', 'CARGADORES Y ESTACIONAMIENTO CENTRAL ', 160.00, 'Efectivo', 'Central'),
(100, '2025-08-03', 'GASOLINA CAMIONETA LEO ', 500.00, 'Efectivo', 'Inversiones'),
(101, '2025-08-03', 'UBER CASA LEO IDA', 140.00, 'Efectivo', 'Inversiones'),
(102, '2025-08-04', 'ZANAHORIA', 100.00, 'Efectivo', 'Central'),
(103, '2025-08-04', 'CARGADORES CENTRAL ', 100.00, 'Efectivo', 'Central'),
(104, '2025-08-06', 'CHICHARRON ', 1760.00, 'Efectivo', 'Inversiones'),
(105, '2025-08-05', 'YOLI CHILAPA ', 2550.00, 'Tarjeta', 'Inversiones'),
(106, '2025-08-04', 'TOSTADAS ', 1218.00, 'Tarjeta', 'Inversiones'),
(107, '2025-08-05', 'AGUA ', 403.00, 'Tarjeta', 'Inversiones'),
(108, '2025-08-01', 'AGUA ', 341.00, 'Tarjeta', 'Inversiones'),
(109, '2025-08-08', 'PAGO VICKY (maiz)', 6640.00, 'Tarjeta', 'Inversiones'),
(110, '2025-08-08', 'MEZCAL', 3540.00, 'Tarjeta', 'Inversiones'),
(111, '2025-08-07', 'CENTRAL ', 4787.00, 'Efectivo', 'Central'),
(112, '2025-08-07', 'GASOLINA', 500.00, 'Efectivo', 'Fijo'),
(113, '2025-08-07', 'MERCADO', 8128.80, 'Efectivo', 'Mercado'),
(114, '2025-08-08', 'AGUA', 181.00, 'Tarjeta', ''),
(115, '2025-08-08', 'CLORO Y FABULOSO ', 788.80, 'Tarjeta', 'Inversiones'),
(116, '2025-08-08', 'WALTMART ( nesquick)', 469.00, 'Tarjeta', 'Inversiones'),
(117, '2025-08-09', 'REPARACION REFRI ', 1500.00, 'Tarjeta', 'Mantenimiento'),
(118, '2025-08-09', 'MAIZ', 4800.00, 'Tarjeta', 'Inversiones'),
(119, '2025-08-11', 'TOSTADAS ', 1610.00, 'Tarjeta', 'Inversiones'),
(120, '2025-08-12', 'AGUA ', 558.00, 'Tarjeta', 'Inversiones'),
(121, '2025-08-12', 'COMPLETO MICKY ', 800.00, 'Efectivo', 'Central'),
(122, '2025-08-12', 'GAS', 801.00, 'Tarjeta', 'Fijo'),
(123, '2025-08-12', 'CAMARAS ', 5280.00, 'Efectivo', 'Inversiones'),
(124, '2025-08-15', 'AGUA', 217.00, 'Tarjeta', 'Inversiones'),
(125, '2025-08-18', 'CHALUPA', 750.00, 'Efectivo', 'Inversiones'),
(126, '2025-08-19', 'AGUA ', 558.00, 'Tarjeta', 'Inversiones'),
(127, '2025-08-19', 'TOSTADAS ', 1218.00, 'Tarjeta', 'Inversiones'),
(128, '2025-08-13', 'CENTRAL ', 7617.00, 'Efectivo', 'Central'),
(129, '2025-08-13', 'MERCADO', 7944.00, 'Efectivo', 'Mercado'),
(130, '2025-08-21', 'CENTRAL ', 6004.00, 'Efectivo', 'Central'),
(131, '2025-08-21', 'MERCADO', 8421.20, 'Efectivo', 'Mercado'),
(132, '2025-08-19', 'CHICHARRON', 2100.00, 'Efectivo', 'Inversiones'),
(133, '2025-08-22', 'AGUA ', 186.00, 'Tarjeta', 'Inversiones'),
(134, '2025-08-24', 'TOSTADAS ', 1442.00, 'Tarjeta', 'Inversiones'),
(135, '2025-08-25', 'CARNE MOLIDA', 950.00, 'Efectivo', 'Inversiones'),
(136, '2025-08-25', 'SECOS', 2500.00, 'Tarjeta', 'Central'),
(137, '2025-08-26', 'AGUA ', 620.00, 'Tarjeta', 'Inversiones'),
(138, '2025-08-18', 'SUELDO ANDREA', 4500.00, 'Efectivo', 'Fijo'),
(139, '2025-08-15', 'EXTRAS CENTRAL ', 400.00, 'Efectivo', 'Inversiones'),
(140, '2025-08-27', 'EXTRAS CENTRAL ALFONSO ', 1000.00, 'Efectivo', 'Inversiones'),
(141, '2025-08-27', 'SECOS 2 PAGO ', 2106.00, 'Tarjeta', 'Inversiones'),
(142, '2025-08-27', 'SECOS 3 PAGO ', 1000.00, 'Efectivo', 'Inversiones'),
(143, '2025-08-27', 'CHALUPA', 1200.00, 'Efectivo', 'Inversiones'),
(144, '2025-08-27', 'CREMERIA ', 4832.00, 'Efectivo', 'Mercado'),
(145, '2025-08-27', 'ENVIO ', 1100.00, 'Efectivo', 'Inversiones'),
(146, '2025-08-27', 'CHICHARRON', 1310.00, 'Efectivo', 'Inversiones'),
(147, '2025-08-27', 'VIAJES TONA ', 2590.00, 'Efectivo', 'Inversiones'),
(148, '2025-08-28', 'CENTRAL ', 8090.00, 'Efectivo', ''),
(149, '2025-08-28', 'MERCADO', 7579.00, 'Efectivo', 'Mercado'),
(150, '2025-08-24', 'SAMS ', 1684.00, 'Tarjeta', 'Inversiones'),
(152, '2025-08-28', 'COSTCO ', 4973.80, 'Tarjeta', 'Inversiones'),
(153, '2025-08-29', 'ARERO', 4604.71, 'Tarjeta', 'Inversiones'),
(154, '2025-09-01', 'Tostadas', 1610.00, 'Tarjeta', 'Inversiones'),
(155, '2025-09-01', 'Walmart Super (001930)', 2233.00, 'Tarjeta', 'Inversiones'),
(156, '2025-09-02', 'Cloro (448705531)', 928.00, 'Tarjeta', 'Inversiones'),
(157, '2025-09-02', 'Agua (13202216)', 651.00, 'Tarjeta', 'Inversiones'),
(158, '2025-09-03', 'Chicharrón (5906136502)', 1960.00, 'Tarjeta', 'Inversiones'),
(159, '2025-09-03', 'Costco Lysol y Hershey (095922)', 788.00, 'Tarjeta', 'Inversiones'),
(160, '2025-09-04', 'Gasto Mercado', 8287.00, 'Efectivo', 'Mercado'),
(161, '2025-09-04', 'Gasto Central A.', 6249.00, 'Efectivo', 'Central'),
(162, '2025-08-29', 'Arero (6436) (164762) (164759)', 5979.77, 'Efectivo', 'Inversiones'),
(163, '2025-09-05', 'Agua (358292596)', 310.00, 'Tarjeta', 'Inversiones'),
(164, '2025-09-04', 'Arero devolución  tapa (164948)', 498.04, 'Efectivo', 'Inversiones'),
(165, '2025-09-08', 'Tostadas (577493605)', 1260.00, 'Efectivo', 'Inversiones'),
(166, '2025-09-07', 'Costco Maggi 800ml PTD Almacén (099559)', 418.00, 'Efectivo', 'Inversiones'),
(167, '2025-09-02', 'Sams Filtros Cafe', 270.00, 'Efectivo', 'Inversiones');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Pagos`
--

CREATE TABLE `Pagos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `Metodo` enum('Tarjeta','Efectivo') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Pagos`
--

INSERT INTO `Pagos` (`id`, `descripcion`, `monto`, `fecha`, `Metodo`, `created_at`) VALUES
(1, 'PAGO DORADA ', 16000.00, '2025-07-06', 'Efectivo', '2025-07-14 18:09:51'),
(2, 'PAGO DORADA DEL 23 AL 29 ', 8500.00, '2025-06-29', 'Efectivo', '2025-07-14 18:12:27'),
(3, 'DORADA 16 AL 22 ', 10700.00, '2025-06-22', 'Efectivo', '2025-07-15 20:49:07'),
(4, 'MES MAYO FINANZAS', 12818.00, '2025-06-02', 'Efectivo', '2025-07-15 20:50:42'),
(5, 'DORADA 16 AL 22 ', 28156.59, '2025-06-22', 'Tarjeta', '2025-07-15 20:53:18'),
(6, 'DORADA 23 AL 29 ', 14762.00, '2025-06-29', 'Tarjeta', '2025-07-15 20:54:13'),
(7, 'DORADA 30 AL 06 ', 14333.16, '2025-07-16', 'Tarjeta', '2025-07-16 19:50:39'),
(8, 'DORADA 07 AL 13 ', 16525.87, '2025-07-16', 'Tarjeta', '2025-07-16 20:09:30'),
(9, 'DORADA 07 AL 13 ', 9425.00, '2025-07-16', 'Efectivo', '2025-07-16 20:15:56'),
(10, 'DORADA 14 AL 20', 14328.84, '2025-07-21', 'Tarjeta', '2025-07-21 21:21:33'),
(11, 'DORADA 14 AL 20', 7100.00, '2025-07-21', 'Efectivo', '2025-07-21 21:22:38'),
(12, 'DORADA 21 AL 27', 6100.00, '2025-07-28', 'Efectivo', '2025-07-28 21:38:04'),
(13, 'DORADA 21 AL 27', 29130.00, '2025-07-28', 'Tarjeta', '2025-07-28 21:38:23'),
(15, 'MES FINANZAS JULIO ', 11235.00, '2025-07-28', 'Efectivo', '2025-07-28 22:01:04'),
(16, 'DORADA 20 AL 03 DE AGOSTO ', 16155.78, '2025-08-07', 'Tarjeta', '2025-08-08 19:55:19'),
(17, 'DORADA 20 AL 03 DE AGOSTO ', 9500.00, '2025-08-07', 'Efectivo', '2025-08-08 19:55:38'),
(18, 'PAGO FINANZAS JULIO ', 4426.74, '2025-08-08', 'Tarjeta', '2025-08-08 20:25:56'),
(19, 'DORADA 04 AL 10 AGOSTO ', 17700.00, '2025-08-12', 'Efectivo', '2025-08-12 19:54:34'),
(20, 'DORADA 04 AL 10 AGOSTO ', 6007.08, '2025-08-12', 'Tarjeta', '2025-08-12 19:54:54'),
(21, 'DORADA 11 AL 17 ', 17400.00, '2025-08-21', 'Efectivo', '2025-08-27 15:55:33'),
(22, 'DORADA 11 AL 17 ', 6379.00, '2025-08-21', 'Tarjeta', '2025-08-27 15:56:20'),
(23, 'PAGO FINANZAS 1 AL 24 AGOSTO ', 19590.00, '2025-08-27', 'Efectivo', '2025-08-27 21:29:00'),
(25, 'DORADA 16 AL 24', 15400.00, '2025-08-27', 'Efectivo', '2025-08-29 18:33:10'),
(26, 'DORADA 16 AL 24', 14921.00, '2025-08-27', 'Tarjeta', '2025-08-29 21:02:46'),
(27, 'DORADA 25 AL 31 AGOSTO ', 7700.00, '2025-08-31', 'Efectivo', '2025-09-01 18:25:00'),
(28, 'DORADA 25 AL 31 AGOSTO ', 21536.38, '2025-08-31', 'Tarjeta', '2025-09-01 18:26:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ultima_actividad` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Sucursales`
--

CREATE TABLE `Sucursales` (
  `SucursalID` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf32_spanish_ci NOT NULL,
  `direccion` text COLLATE utf32_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `Sucursales`
--

INSERT INTO `Sucursales` (`SucursalID`, `nombre`, `direccion`) VALUES
(1, 'Kalli Express Finanzas', 'Dirección ficticia'),
(2, 'Kalli Dorada', 'Dirección ficticia'),
(4, 'Kalli Cholula', 'Dirección Ficticia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_completo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `token_reset` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_reset_expira` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password_hash`, `nombre_completo`, `activo`, `ultimo_login`, `token_reset`, `token_reset_expira`, `creado_en`, `actualizado_en`) VALUES
(1, 'admin', 'admin@gastosapp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 1, NULL, NULL, NULL, '2025-09-09 02:25:47', '2025-09-09 02:25:47');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_gastos_mensuales`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_gastos_mensuales` (
`anio` int(4)
,`mes` int(2)
,`Tipo` enum('Fijo','Central','Mercado','Mantenimiento','Inversiones')
,`Metodo` enum('Tarjeta','Efectivo')
,`cantidad_gastos` bigint(21)
,`total_monto` decimal(32,2)
,`promedio_monto` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_pagos_mensuales`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_pagos_mensuales` (
`anio` int(4)
,`mes` int(2)
,`Metodo` enum('Tarjeta','Efectivo')
,`cantidad_pagos` bigint(21)
,`total_monto` decimal(32,2)
,`promedio_monto` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_resumen_financiero`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_resumen_financiero` (
`tipo` varchar(6)
,`anio` bigint(20)
,`mes` bigint(20)
,`total` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_gastos_mensuales`
--
DROP TABLE IF EXISTS `vista_gastos_mensuales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cpses_ka4t9agz7i`@`localhost` SQL SECURITY DEFINER VIEW `vista_gastos_mensuales`  AS SELECT year(`Gastos`.`Fecha`) AS `anio`, month(`Gastos`.`Fecha`) AS `mes`, `Gastos`.`Tipo` AS `Tipo`, `Gastos`.`Metodo` AS `Metodo`, count(0) AS `cantidad_gastos`, sum(`Gastos`.`Monto`) AS `total_monto`, avg(`Gastos`.`Monto`) AS `promedio_monto` FROM `Gastos` GROUP BY year(`Gastos`.`Fecha`), month(`Gastos`.`Fecha`), `Gastos`.`Tipo`, `Gastos`.`Metodo` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_pagos_mensuales`
--
DROP TABLE IF EXISTS `vista_pagos_mensuales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cpses_ka4t9agz7i`@`localhost` SQL SECURITY DEFINER VIEW `vista_pagos_mensuales`  AS SELECT year(`Pagos`.`fecha`) AS `anio`, month(`Pagos`.`fecha`) AS `mes`, `Pagos`.`Metodo` AS `Metodo`, count(0) AS `cantidad_pagos`, sum(`Pagos`.`monto`) AS `total_monto`, avg(`Pagos`.`monto`) AS `promedio_monto` FROM `Pagos` GROUP BY year(`Pagos`.`fecha`), month(`Pagos`.`fecha`), `Pagos`.`Metodo` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_resumen_financiero`
--
DROP TABLE IF EXISTS `vista_resumen_financiero`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cpses_ka4t9agz7i`@`localhost` SQL SECURITY DEFINER VIEW `vista_resumen_financiero`  AS SELECT 'Gastos' AS `tipo`, year(`Gastos`.`Fecha`) AS `anio`, month(`Gastos`.`Fecha`) AS `mes`, sum(`Gastos`.`Monto`) AS `total` FROM `Gastos` GROUP BY year(`Gastos`.`Fecha`), month(`Gastos`.`Fecha`)union all select 'Pagos' AS `tipo`,year(`Pagos`.`fecha`) AS `anio`,month(`Pagos`.`fecha`) AS `mes`,sum(`Pagos`.`monto`) AS `total` from `Pagos` group by year(`Pagos`.`fecha`),month(`Pagos`.`fecha`)  ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `Gastos`
--
ALTER TABLE `Gastos`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_fecha` (`Fecha`),
  ADD KEY `idx_tipo` (`Tipo`),
  ADD KEY `idx_metodo` (`Metodo`),
  ADD KEY `idx_fecha_tipo` (`Fecha`,`Tipo`);

--
-- Indices de la tabla `Pagos`
--
ALTER TABLE `Pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_metodo` (`Metodo`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_ultima_actividad` (`ultima_actividad`);

--
-- Indices de la tabla `Sucursales`
--
ALTER TABLE `Sucursales`
  ADD PRIMARY KEY (`SucursalID`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_ultimo_login` (`ultimo_login`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `Gastos`
--
ALTER TABLE `Gastos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT de la tabla `Pagos`
--
ALTER TABLE `Pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `Sucursales`
--
ALTER TABLE `Sucursales`
  MODIFY `SucursalID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
