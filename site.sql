-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 03 avr. 2026 à 08:34
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `site`
--

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

DROP TABLE IF EXISTS `cours`;
CREATE TABLE IF NOT EXISTS `cours` (
  `titre` varchar(55) NOT NULL,
  `fichier` varchar(55) NOT NULL,
  `date_modification` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `contenu` varchar(200) NOT NULL,
  `user_id` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`titre`, `fichier`, `date_modification`, `contenu`, `user_id`) VALUES
('Cours du 04/07/2025 07:45:41', '', '02/07/2025 10:27:50', '', 1),
('Cours du 02/07/2025 10:27:50', '', '02/07/2025 10:27:50', '', 1),
('Cours du 20/01/2026 08:34:17', 'test33.md', '20/01/2026 08:34:17', '# Bienvenue\n\nÉcris ici ton cours en Markdown.\n# arrrarararaara', 17),
('Cours du 03/02/2026 08:14:43', 'test.md', '03/02/2026 08:14:43', '# Bienvenue\n\nÉcris ici ton cours en Markdown.\n', 17),
('Cours du 03/03/2026 16:09:24', 'cc.md', '03/03/2026 16:09:24', '# Bienvenue\nc\nÉcris ici ton cours en Markdown.\n', 18);

-- --------------------------------------------------------

--
-- Structure de la table `qcm`
--

DROP TABLE IF EXISTS `qcm`;
CREATE TABLE IF NOT EXISTS `qcm` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) DEFAULT NULL,
  `temps` int DEFAULT NULL,
  `cours` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `qcm`
--

INSERT INTO `qcm` (`id`, `titre`, `temps`, `cours`, `module`, `date_creation`, `user_id`) VALUES
(1, 'IUH', 67, 'MELEC', 'Module 1', '2026-03-24 16:17:04', 18),
(2, 'IUH (copie)', 67, 'MELEC', 'Module 1', '2026-03-24 16:18:05', 18),
(3, 'test 3', 64, 'MELEC', 'Module 1', '2026-03-24 16:18:57', 18),
(8, 'test bq', 12, 'MEE', 'Module 3', '2026-03-31 11:52:24', 18);

-- --------------------------------------------------------

--
-- Structure de la table `qcm_questions`
--

DROP TABLE IF EXISTS `qcm_questions`;
CREATE TABLE IF NOT EXISTS `qcm_questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `qcm_id` int NOT NULL,
  `question` text NOT NULL,
  `choix_1` varchar(255) NOT NULL,
  `choix_2` varchar(255) NOT NULL,
  `choix_3` varchar(255) DEFAULT NULL,
  `choix_4` varchar(255) DEFAULT NULL,
  `bonne_reponse` int NOT NULL COMMENT '1, 2, 3 ou 4',
  `ordre` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `qcm_id` (`qcm_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `qcm_questions`
--

INSERT INTO `qcm_questions` (`id`, `qcm_id`, `question`, `choix_1`, `choix_2`, `choix_3`, `choix_4`, `bonne_reponse`, `ordre`) VALUES
(3, 1, 'est ce que yassine est multi millionnaire', 'non', 'oui', '', '', 1, 1),
(4, 1, 'ça va', 'non', 'non', '', '', 2, 2),
(5, 1, 'test', 'test', 'nn', '', '', 1, 3),
(12, 2, 'ça va', 'non', 'non', '', '', 2, 2),
(11, 2, 'est ce que yassine est multi millionnaire', 'non', 'oui', '', '', 2, 1),
(9, 3, 'est ce que yassine est multi millionnaire', 'non', 'oui', '', '', 1, 1),
(10, 3, 'ça va', 'non', 'non', '', '', 2, 2),
(13, 2, 'test', 'test', 'nn', '', '', 1, 3),
(20, 8, 'ça dit quoi', 'rien', 'miaou', '', '', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `questions_banque`
--

DROP TABLE IF EXISTS `questions_banque`;
CREATE TABLE IF NOT EXISTS `questions_banque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `matiere` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `question` text NOT NULL,
  `choix_1` varchar(255) NOT NULL,
  `choix_2` varchar(255) NOT NULL,
  `choix_3` varchar(255) DEFAULT NULL,
  `choix_4` varchar(255) DEFAULT NULL,
  `bonne_reponse` int NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(55) NOT NULL,
  `prenom` varchar(55) NOT NULL,
  `formation` varchar(55) NOT NULL,
  `classe` varchar(55) DEFAULT NULL,
  `status` varchar(55) NOT NULL,
  `date` datetime(6) NOT NULL,
  `password` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `formation`, `classe`, `status`, `date`, `password`) VALUES
(16, 'Burdloff', 'Rayan', 'CIEL', 'CIEL2', 'eleve', '2026-01-20 07:17:56.000000', 'TrNl29zA4w'),
(5, 'admin', 'admin', '', NULL, 'admin', '2026-01-19 16:42:28.000000', 'Admin1234'),
(17, 'Burri', 'Jerome', 'CIEL', NULL, 'prof', '2026-01-20 07:18:12.000000', 'c'),
(15, 'Raboteur', 'Tiago', 'CIEL', 'CIEL2', 'eleve', '2026-01-19 00:00:00.000000', 'c'),
(18, 'Mohktar', 'Momo', 'MFER', NULL, 'prof', '2026-02-09 13:24:24.000000', 'c');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
