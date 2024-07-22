-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 20, 2024 at 09:39 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecoleencg`
--

-- --------------------------------------------------------

--
-- Table structure for table `grp`
--

DROP TABLE IF EXISTS `grp`;
CREATE TABLE IF NOT EXISTS `grp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` int NOT NULL,
  `year` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `filiere` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `extra_info` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grp`
--

INSERT INTO `grp` (`id`, `name`, `nombre`, `year`, `filiere`, `extra_info`) VALUES
(19, '1A', 150, '1er', '-', 'Section A'),
(20, '5A', 50, '5ème', 'Filiere1', '-'),
(22, '1A', 200, '1er', '-', 'Section B'),
(24, '2A', 120, '2ème', '-', 'Section A'),
(25, '2A', 130, '2ème', '-', 'Section B'),
(26, '3A', 140, '3ème', '-', 'Section A'),
(27, '3A', 150, '3ème', '-', 'Section B'),
(28, '4A', 160, '4ème', 'Filiere1', '-'),
(29, '4A', 170, '4ème', 'Filiere2', '-'),
(30, '5A', 180, '5ème', 'Filiere3', '-'),
(31, '5A', 190, '5ème', 'Filiere4', '-');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
