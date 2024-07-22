-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 22, 2024 at 11:51 AM
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
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
CREATE TABLE IF NOT EXISTS `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int DEFAULT NULL,
  `salle_id` int DEFAULT NULL,
  `professeur_id` int DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `jour_par_semaine` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `semester_id` int DEFAULT NULL,
  `subject_id` int DEFAULT NULL,
  `type_seance` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `salle_id` (`salle_id`),
  KEY `professeur_id` (`professeur_id`),
  KEY `semester_id` (`semester_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`id`, `group_id`, `salle_id`, `professeur_id`, `reservation_date`, `start_time`, `end_time`, `jour_par_semaine`, `semester_id`, `subject_id`, `type_seance`) VALUES
(1, 19, 11, 1, '2024-07-22', '09:00:00', '10:30:00', 'Lundi', 1, 4, NULL),
(2, 20, 11, 2, '2024-07-23', '10:45:00', '12:15:00', 'Mardi', 1, 5, NULL),
(3, 24, 20, 3, '2024-07-24', '14:00:00', '15:30:00', 'Mercredi', 1, 6, NULL),
(4, 24, 10, 4, '2024-07-25', '15:45:00', '17:15:00', 'Jeudi', 1, 7, NULL),
(5, 22, 10, 5, '2024-07-26', '09:00:00', '10:30:00', 'Vendredi', 1, 8, NULL),
(6, 24, 13, 6, '2024-07-22', '10:45:00', '12:15:00', 'Lundi', 1, 4, NULL),
(7, 20, 14, 7, '2024-07-23', '14:00:00', '15:30:00', 'Mardi', 1, 5, NULL),
(8, 20, 15, 8, '2024-07-24', '09:00:00', '10:30:00', 'Mercredi', 1, 6, NULL),
(9, 19, 12, 9, '2024-07-25', '10:45:00', '12:15:00', 'Jeudi', 1, 7, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
