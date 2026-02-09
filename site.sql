-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 20 jan. 2026 à 08:47
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
('Cours du 20/01/2026 08:34:17', 'test33.md', '20/01/2026 08:34:17', '# Bienvenue\n\nÉcris ici ton cours en Markdown.\n# arrrarararaara', 17);

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
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `formation`, `classe`, `status`, `date`, `password`) VALUES
(16, 'Burdloff', 'Rayan', 'CIEL', 'CIEL2', 'eleve', '2026-01-20 07:17:56.000000', 'TrNl29zA4w'),
(5, 'admin', 'admin', '', NULL, 'admin', '2026-01-19 16:42:28.000000', 'Admin1234'),
(17, 'Burri', 'Jerome', 'CIEL', NULL, 'prof', '2026-01-20 07:18:12.000000', 'AwybKuNV%R'),
(15, 'Raboteur', 'Tiago', 'CIEL', 'CIEL2', 'eleve', '2026-01-19 00:00:00.000000', 'okokok');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
