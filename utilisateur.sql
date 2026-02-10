-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 02:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartnexus`
--

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL,
  `is_verified` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `photo` varchar(500) DEFAULT NULL,
  `bio` longtext DEFAULT NULL,
  `expertise` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `roles`, `password`, `phone_number`, `is_active`, `is_verified`, `created_at`, `last_login_at`, `nom`, `prenom`, `photo`, `bio`, `expertise`) VALUES
(5, 'admin@smartnexus.ai', '[\"ROLE_ADMIN\"]', '$2y$13$rPaQHSPkonfJF8ghMC284Ox6s.LGBe.GczTfIjUGmOXcwzWy9jYtG', '+33601020304', 1, 1, '2026-02-04 23:32:45', NULL, 'Administrateur', 'Système', '/uploads/profile_photos/image-removebg-preview-7-removebg-preview-1-69871d17ecaa3.png', 'Administrateur principal du système SmartNexus AI', NULL),
(6, 'employee@smartnexus.ai', '[\"ROLE_EMPLOYEE\"]', '$2y$13$.k7V/ZLpzeoLPlXvdy.Yp.ddiqNlEl.Qxg9QBWylVQRV2.ZHw7nUm', '+33612345678', 1, 1, '2026-02-04 23:32:45', NULL, 'Dupont', 'Jean', NULL, 'Chef de projet senior avec 5 ans d\'expérience', 'Gestion de projet, Développement Agile'),
(54, 'candidat@smartnexus.ai', '[\"ROLE_CLIENT\"]', '$2y$13$JOhad.d0MX/3KGjXrhtxSuO/ekkJlQRGcgcI9kqvW/c3qkDAuusja', '+33698765432', 1, 1, '2026-02-04 23:32:46', NULL, 'Martin', 'Sophie', NULL, 'Développeur full-stack passionné par les nouvelles technologies', 'PHP, Symfony, React, Vue.js, Node.js'),
(10, '3rsafco1wg@ibolinva.com', '[\"ROLE_CLIENT\"]', '$2y$13$cun.tE8jonaXZOruV3BB4uNlcrCVh8epwngj0ud4dqfO57vuzrUBm', '+21646746146', 1, 1, '2026-02-05 01:04:40', NULL, 'mppoinh', 'sertgvff', NULL, NULL, NULL),
(11, '47jmmtadtr@ozsaip.com', '[\"ROLE_EMPLOYEE\"]', '$2y$13$ZgNB5iLsY6ACcWHLJMxuz./Dnm0hobq8IZnAkRh5Dgwp4zx9VsHXW', '+21646746146', 1, 1, '2026-02-05 01:06:01', NULL, 'vfgtryjhngb', 'vvgtrvv', NULL, NULL, NULL),
(12, 'ahmedmhirsi955@gmail.com', '[\"ROLE_EMPLOYEE\"]', '$2y$13$cuiHUBtQ5cPu66PJoNMB..piWKGHnX9Ro2SHLVT/2zOTZx9dxesze', '+21646746146', 1, 1, '2026-02-05 11:36:32', NULL, 'mhirsi', 'ahmed', NULL, NULL, NULL),
(13, 'yipinab438@aixind.com', '[\"ROLE_CLIENT\"]', '$2y$13$WE9XNtg4e2LSKdmA/XM2teetgFImaSItlpZvEATRYR9SpuaE9qXgG', '+21646746146', 1, 1, '2026-02-05 21:02:58', NULL, 'client8552', 'client 545', NULL, 'hhhhhhh', 'omar modifeha'),
(14, 'vafini4328@azeriom.com', '[\"ROLE_CLIENT\"]', '$2y$13$q/m6IDwACZe3dfn.Lu1E.ebag0K2QzJFtr5ejm7mVCldX2iYp9HPm', '+21646746144', 1, 1, '2026-02-07 14:03:34', NULL, 'loiuyrfvn', 'bhytghn', NULL, 'sfvdbfvdfd', 'LINKEDIN CEO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_1D1C63B3E7927C74` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
