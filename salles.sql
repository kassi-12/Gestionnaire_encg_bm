-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 20, 2024 at 09:33 AM
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
-- Table structure for table `salles`
--

DROP TABLE IF EXISTS `salles`;
CREATE TABLE IF NOT EXISTS `salles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `capacity` int NOT NULL,
  `features` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `capacity_exam` int DEFAULT NULL,
  `room_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salles`
--

INSERT INTO `salles` (`id`, `name`, `capacity`, `features`, `capacity_exam`, `room_type`) VALUES
(10, 'A1', 123, 'Accès à l\'internet,Équipements du réseau,Vidéo projecteur,Climatiseur', 66, 'cours,TD,TP,soutenance'),
(11, 'B1', 150, 'Tableau interactif,Wi-Fi,Climatisation', 75, 'cours,TD'),
(12, 'C1', 200, 'Ordinateurs,Wi-Fi,Climatisation', 100, 'TP,soutenance'),
(13, 'D1', 80, 'Vidéo projecteur,Wi-Fi,Climatisation', 40, 'cours,TD'),
(14, 'E1', 90, 'Tableau blanc,Wi-Fi,Climatisation', 45, 'cours,TP'),
(15, 'F1', 100, 'Vidéo projecteur,Wi-Fi,Climatisation', 50, 'soutenance'),
(16, 'G1', 110, 'Tableau interactif,Wi-Fi,Climatisation', 55, 'cours,TD,TP'),
(17, 'H1', 120, 'Ordinateurs,Wi-Fi,Climatisation', 60, 'TP'),
(18, 'I1', 130, 'Vidéo projecteur,Wi-Fi,Climatisation', 65, 'cours,TD,soutenance'),
(19, 'J1', 140, 'Tableau blanc,Wi-Fi,Climatisation', 70, 'cours,TP,soutenance'),
(20, 'K1', 160, 'Tableau interactif,Wi-Fi,Climatisation', 80, 'cours,TD,TP,soutenance');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
