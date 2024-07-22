-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 22, 2024 at 12:10 PM
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
-- Table structure for table `evenement`
--

DROP TABLE IF EXISTS `evenement`;
CREATE TABLE IF NOT EXISTS `evenement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `salle_id` int NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `organizer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salle_id` (`salle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evenement`
--

INSERT INTO `evenement` (`id`, `event_name`, `salle_id`, `event_date`, `start_time`, `end_time`, `organizer`) VALUES
(1, 'TecDay', 11, '2024-07-30', '15:45:00', '17:15:00', 'Microsoft');

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

-- --------------------------------------------------------

--
-- Table structure for table `professeur`
--

DROP TABLE IF EXISTS `professeur`;
CREATE TABLE IF NOT EXISTS `professeur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `gsm` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professeur`
--

INSERT INTO `professeur` (`id`, `first_name`, `last_name`, `email`, `gsm`) VALUES
(3, 'Mounir', 'el Amrani', 'mounirelamrani@example.com', '0612345678'),
(4, 'Fatima', 'Bennani', 'fatimabennani@example.com', '0623456789'),
(5, 'Ahmed', 'Naciri', 'ahmednaciri@example.com', '0634567890'),
(6, 'Sara', 'El Kadi', 'saraelkadi@example.com', '0645678901'),
(7, 'Khalid', 'Alami', 'khalidalami@example.com', '0656789012'),
(8, 'Hicham', 'Benzakour', 'hichambenzakour@example.com', '0667890123'),
(9, 'Rachid', 'Oulad', 'rachidoulad@example.com', '0678901234'),
(10, 'Youssef', 'El Fassi', 'youssefelfassi@example.com', '0689012345'),
(11, 'Imane', 'Chaoui', 'imanechaoui@example.com', '0690123456'),
(12, 'Omar', 'Touil', 'omartouil@example.com', '0601122334');

-- --------------------------------------------------------

--
-- Table structure for table `rattrapage`
--

DROP TABLE IF EXISTS `rattrapage`;
CREATE TABLE IF NOT EXISTS `rattrapage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int DEFAULT NULL,
  `rattrapage_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `comments` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rattrapage`
--

INSERT INTO `rattrapage` (`id`, `reservation_id`, `rattrapage_date`, `start_time`, `end_time`, `comments`) VALUES
(1, 1, '2024-07-29', '09:00:00', '10:30:00', 'Rattrapage du Lundi'),
(2, 2, '2024-07-30', '10:45:00', '12:15:00', 'Rattrapage du Mardi');

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
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(9, 19, 12, 9, '2024-07-25', '10:45:00', '12:15:00', 'Jeudi', 1, 7, NULL),
(24, 19, 11, 3, '2024-07-23', '09:00:00', '10:30:00', 'mardi', 1, 4, 'cours');

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

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

DROP TABLE IF EXISTS `semesters`;
CREATE TABLE IF NOT EXISTS `semesters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`) VALUES
(1, 'Semestre 1'),
(2, 'Semestre 2'),
(3, 'Semestre 3'),
(4, 'Semestre 4'),
(5, 'Semestre 5'),
(6, 'Semestre 6'),
(7, 'Semestre 7'),
(8, 'Semestre 8'),
(9, 'Semestre 9'),
(10, 'Semestre 10');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `subject_id` int NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `semester_id` int NOT NULL,
  `year` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`subject_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `semester_id`, `year`) VALUES
(4, 'FR', 1, '2ème'),
(5, 'Mathématiques', 2, '1er'),
(6, 'Physique', 2, '2ème'),
(7, 'Chimie', 3, '3ème'),
(8, 'Biologie', 3, '4ème'),
(9, 'Informatique', 4, '5ème'),
(10, 'Économie', 4, '6ème'),
(11, 'Gestion', 5, '1er'),
(12, 'Marketing', 5, '2ème'),
(13, 'Comptabilité', 6, '3ème'),
(14, 'Statistiques', 6, '4ème'),
(15, 'ENG', 1, '1er');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
