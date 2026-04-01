-- phpMyAdmin SQL Dump
-- version 4.6.6deb4+deb9u2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 01, 2026 at 07:55 PM
-- Server version: 10.1.48-MariaDB-0+deb9u2
-- PHP Version: 7.0.33-0+deb9u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studiochroma`
--

-- --------------------------------------------------------

--
-- Table structure for table `amis`
--

CREATE TABLE `amis` (
  `id` int(11) NOT NULL,
  `utilisateur_id_1` int(11) NOT NULL,
  `utilisateur_id_2` int(11) NOT NULL,
  `statut` enum('en_attente','accepte','refuse') DEFAULT 'en_attente',
  `demandeur_id` int(11) NOT NULL,
  `date_demande` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_reponse` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `amis`
--

INSERT INTO `amis` (`id`, `utilisateur_id_1`, `utilisateur_id_2`, `statut`, `demandeur_id`, `date_demande`, `date_reponse`) VALUES
(1, 1, 2, 'accepte', 2, '2026-03-31 09:23:29', '2026-03-31 12:15:58');

-- --------------------------------------------------------

--
-- Table structure for table `evenements`
--

CREATE TABLE `evenements` (
  `id` int(11) NOT NULL,
  `organisateur_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `image_evenement` varchar(255) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `type` enum('prive','partage','public') NOT NULL DEFAULT 'prive',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `evenements`
--

INSERT INTO `evenements` (`id`, `organisateur_id`, `titre`, `description`, `image_evenement`, `date_debut`, `date_fin`, `type`, `date_creation`) VALUES
(1, 2, 'Paint', 'Make your best sea-themed painting', NULL, '2026-05-02 13:25:00', '2026-05-10 09:26:00', 'public', '2026-03-31 09:26:28'),
(2, 3, 't', '', NULL, '2026-04-02 00:14:00', '2026-04-18 02:15:00', 'prive', '2026-04-01 19:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `evenement_participants`
--

CREATE TABLE `evenement_participants` (
  `id` int(11) NOT NULL,
  `evenement_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `statut` enum('en_attente','accepte','refuse') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `langues`
--

CREATE TABLE `langues` (
  `id` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `langues`
--

INSERT INTO `langues` (`id`, `code`, `nom`) VALUES
(1, 'fr', 'Français'),
(2, 'sq', 'Shqip'),
(3, 'vi', 'Tiếng Việt');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `piece_jointe` varchar(255) DEFAULT NULL,
  `lu` tinyint(1) DEFAULT '0',
  `est_invitation` tinyint(1) DEFAULT '0',
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `expediteur_id`, `destinataire_id`, `contenu`, `piece_jointe`, `lu`, `est_invitation`, `date_envoi`) VALUES
(1, 2, 1, 'Salut cv ?', NULL, 1, 1, '2026-03-31 09:23:29'),
(2, 1, 2, 'salut mehdi comment tu va\r\n', NULL, 0, 0, '2026-03-31 12:16:11'),
(3, 3, 3, 'hii', NULL, 1, 1, '2026-04-01 13:26:52');

-- --------------------------------------------------------

--
-- Table structure for table `publications`
--

CREATE TABLE `publications` (
  `id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `photo_publication` varchar(255) DEFAULT NULL,
  `date_publication` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `publications`
--

INSERT INTO `publications` (`id`, `auteur_id`, `contenu`, `image_url`, `photo_publication`, `date_publication`) VALUES
(1, 2, 'Nouvelle peinture', NULL, NULL, '2026-03-31 09:22:26'),
(2, 2, 'what are your creations of the day ?', NULL, NULL, '2026-03-31 09:23:08'),
(3, 1, 'Salut', NULL, NULL, '2026-03-31 10:08:46'),
(4, 3, ':3', NULL, NULL, '2026-04-01 15:11:25'),
(5, 5, 'cv', NULL, NULL, '2026-04-01 17:25:19'),
(6, 5, 'et vous', NULL, NULL, '2026-04-01 17:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `cle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `cle`) VALUES
(2, '3d_modelling'),
(22, 'abstract'),
(23, 'acrylic_gouache'),
(34, 'anatomy'),
(25, 'animation'),
(18, 'caricature'),
(10, 'character_design'),
(26, 'charcoal'),
(27, 'collage'),
(30, 'comics'),
(19, 'digital_illustration'),
(21, 'digital_painting'),
(31, 'handicrafts'),
(11, 'ink_line_art'),
(15, 'journalling'),
(36, 'landscapes'),
(37, 'logo_design'),
(20, 'manga'),
(12, 'mixed_crafts'),
(35, 'mixed_media'),
(8, 'motion_design'),
(1, 'oil_painting'),
(3, 'origami'),
(14, 'paper_quilling'),
(24, 'pencil_sketching'),
(32, 'perspective'),
(17, 'photography'),
(5, 'pottery'),
(28, 'printmaking'),
(16, 'realism'),
(7, 'sculpting'),
(33, 'stopmotion'),
(4, 'storyboarding'),
(29, 'textile'),
(6, 'vector_art'),
(13, 'watercolor'),
(9, 'woodwork');

-- --------------------------------------------------------

--
-- Table structure for table `tags_traductions`
--

CREATE TABLE `tags_traductions` (
  `id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `langue_code` varchar(5) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tags_traductions`
--

INSERT INTO `tags_traductions` (`id`, `tag_id`, `langue_code`, `nom`) VALUES
(1, 1, 'fr', 'Peinture à l\'huile'),
(2, 2, 'fr', 'Modélisation 3D'),
(3, 3, 'fr', 'Origami'),
(4, 4, 'fr', 'Storyboarding'),
(5, 5, 'fr', 'Poterie'),
(6, 6, 'fr', 'Art vectoriel'),
(7, 7, 'fr', 'Sculpture'),
(8, 8, 'fr', 'Motion Design'),
(9, 9, 'fr', 'Travail du bois'),
(10, 10, 'fr', 'Character Design'),
(11, 11, 'fr', 'Encre et trait'),
(12, 12, 'fr', 'Artisanat mixte'),
(13, 13, 'fr', 'Aquarelle'),
(14, 14, 'fr', 'Quilling papier'),
(15, 15, 'fr', 'Journalling'),
(16, 16, 'fr', 'Réalisme'),
(17, 17, 'fr', 'Photographie'),
(18, 18, 'fr', 'Caricature'),
(19, 19, 'fr', 'Illustration numérique'),
(20, 20, 'fr', 'Manga'),
(21, 21, 'fr', 'Peinture numérique'),
(22, 22, 'fr', 'Abstrait'),
(23, 23, 'fr', 'Acrylique / Gouache'),
(24, 24, 'fr', 'Dessin au crayon'),
(25, 25, 'fr', 'Animation'),
(26, 26, 'fr', 'Fusain'),
(27, 27, 'fr', 'Collage'),
(28, 28, 'fr', 'Gravure / Impression'),
(29, 29, 'fr', 'Textile'),
(30, 30, 'fr', 'Bande dessinée'),
(31, 31, 'fr', 'Artisanat'),
(32, 32, 'fr', 'Perspective'),
(33, 33, 'fr', 'Stop Motion'),
(34, 34, 'fr', 'Anatomie'),
(35, 35, 'fr', 'Techniques mixtes'),
(36, 36, 'fr', 'Paysages'),
(37, 37, 'fr', 'Création de logo'),
(38, 1, 'sq', 'Pikturë me vaj'),
(39, 2, 'sq', 'Modelim 3D'),
(40, 3, 'sq', 'Origami'),
(41, 4, 'sq', 'Storyboarding'),
(42, 5, 'sq', 'Poçari'),
(43, 6, 'sq', 'Art vektorial'),
(44, 7, 'sq', 'Skulpturë'),
(45, 8, 'sq', 'Motion Design'),
(46, 9, 'sq', 'Punë me dru'),
(47, 10, 'sq', 'Dizajn personazhi'),
(48, 11, 'sq', 'Bojë dhe vizë'),
(49, 12, 'sq', 'Artizanat i përzier'),
(50, 13, 'sq', 'Akuarel'),
(51, 14, 'sq', 'Quilling letre'),
(52, 15, 'sq', 'Journalling'),
(53, 16, 'sq', 'Realizëm'),
(54, 17, 'sq', 'Fotografi'),
(55, 18, 'sq', 'Karikaturë'),
(56, 19, 'sq', 'Ilustrim dixhital'),
(57, 20, 'sq', 'Manga'),
(58, 21, 'sq', 'Pikturë dixhitale'),
(59, 22, 'sq', 'Abstrakt'),
(60, 23, 'sq', 'Akrilik / Guash'),
(61, 24, 'sq', 'Vizatim me laps'),
(62, 25, 'sq', 'Animacion'),
(63, 26, 'sq', 'Qymyr'),
(64, 27, 'sq', 'Kolazh'),
(65, 28, 'sq', 'Printim artistik'),
(66, 29, 'sq', 'Tekstil'),
(67, 30, 'sq', 'Komike'),
(68, 31, 'sq', 'Punë dore'),
(69, 32, 'sq', 'Perspektivë'),
(70, 33, 'sq', 'Stop Motion'),
(71, 34, 'sq', 'Anatomi'),
(72, 35, 'sq', 'Teknika të përziera'),
(73, 36, 'sq', 'Peizazhe'),
(74, 37, 'sq', 'Dizajn logoje'),
(75, 1, 'vi', 'Tranh sơn dầu'),
(76, 2, 'vi', 'Mô hình 3D'),
(77, 3, 'vi', 'Nghệ thuật gấp giấy'),
(78, 4, 'vi', 'Kịch bản hình ảnh'),
(79, 5, 'vi', 'Gốm sứ'),
(80, 6, 'vi', 'Nghệ thuật vector'),
(81, 7, 'vi', 'Điêu khắc'),
(82, 8, 'vi', 'Thiết kế chuyển động'),
(83, 9, 'vi', 'Nghề mộc'),
(84, 10, 'vi', 'Thiết kế nhân vật'),
(85, 11, 'vi', 'Mực và nét vẽ'),
(86, 12, 'vi', 'Thủ công hỗn hợp'),
(87, 13, 'vi', 'Màu nước'),
(88, 14, 'vi', 'Quilling giấy'),
(89, 15, 'vi', 'Nhật ký sáng tạo'),
(90, 16, 'vi', 'Chủ nghĩa hiện thực'),
(91, 17, 'vi', 'Nhiếp ảnh'),
(92, 18, 'vi', 'Biếm họa'),
(93, 19, 'vi', 'Minh họa kỹ thuật số'),
(94, 20, 'vi', 'Manga'),
(95, 21, 'vi', 'Vẽ kỹ thuật số'),
(96, 22, 'vi', 'Trừu tượng'),
(97, 23, 'vi', 'Acrylic / Gouache'),
(98, 24, 'vi', 'Phác thảo chì'),
(99, 25, 'vi', 'Hoạt hình'),
(100, 26, 'vi', 'Than chì'),
(101, 27, 'vi', 'Cắt dán nghệ thuật'),
(102, 28, 'vi', 'In ấn nghệ thuật'),
(103, 29, 'vi', 'Dệt may'),
(104, 30, 'vi', 'Truyện tranh'),
(105, 31, 'vi', 'Thủ công mỹ nghệ'),
(106, 32, 'vi', 'Phối cảnh'),
(107, 33, 'vi', 'Stop Motion'),
(108, 34, 'vi', 'Giải phẫu học'),
(109, 35, 'vi', 'Kỹ thuật hỗn hợp'),
(110, 36, 'vi', 'Phong cảnh'),
(111, 37, 'vi', 'Thiết kế logo');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `pseudonyme` varchar(50) NOT NULL,
  `email` varchar(191) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `photo_profil` varchar(255) DEFAULT 'default.png',
  `langues_parlees` varchar(255) DEFAULT '',
  `nationalite` varchar(100) DEFAULT '',
  `date_naissance` date DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `ville` varchar(100) DEFAULT '',
  `bio` text,
  `experiences_texte` text,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `cgu_acceptees` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `pseudonyme`, `email`, `mot_de_passe`, `photo_profil`, `langues_parlees`, `nationalite`, `date_naissance`, `latitude`, `longitude`, `ville`, `bio`, `experiences_texte`, `date_inscription`, `cgu_acceptees`) VALUES
(1, 'Adam', 'Adam@gmail.com', '$2y$10$Z7mgh7xIPLtI3rLJOAH6YeVj06M9AOnt1RH7I4DJXtX44859Jki2e', 'default.png', 'English', 'Français', '2000-12-26', '0.0000000', '0.0000000', 'Aulnay', '', '', '2026-03-30 15:28:13', 1),
(2, 'Mehdi', 'mehdi@gmail.com', '$2y$10$RjBZWsfR/6DcwSjOIJGjsOwART52rf8T/te9GzsWLihc0JmiMIsC6', 'avatar_69cb761671b4c.jpeg', 'Français,English', 'French', '2004-09-30', '0.0000000', '0.0000000', 'Villepinte', 'Un bon', 'Je fais de la peinture tout les jours', '2026-03-31 09:21:58', 1),
(3, 'yukialie', 'yukialie2006@gmail.com', '$2y$10$y/DUpKIHtFyv0r8ZPC588e/2jXSx8KmK3NG1IWTCaB9GmPSI0dbXC', 'default.png', '', '', '0000-00-00', '0.0000000', '0.0000000', '', '', '', '2026-04-01 07:06:04', 1),
(4, 'nzjc06', 'enzo.sandrasegaram@gmail.com', '$2y$10$2yOLZu3LpJsbVWEZOOuQbu1oIegyzFOxplMcatlm/7hA9erEqdnWu', 'default.png', 'Français', 'France', '0000-00-00', '0.0000000', '0.0000000', 'Aulnay', 'F', 'F', '2026-04-01 14:07:00', 1),
(5, 'lala', 'lala@lala', '$2y$10$lIWVtxM2mtK9OQWBYG7UouZ5PeZltd.q.3NMKyBDUH6UNRclNhRqG', 'default.png', 'Français', 'France', '2006-06-06', '0.0000000', '0.0000000', 'Drancy', 'J\'aime les chats', 'j\'ai les crayons', '2026-04-01 17:25:06', 1),
(6, 'aaaa', 'ada@gmail.com', '$2y$10$hYlPygoJj/PHVCxNXakEwuDHqbiyx3cmIuPAW58TgJzL3XiAKQUoy', 'default.png', '', '', '0000-00-00', '0.0000000', '0.0000000', '', '', '', '2026-04-01 17:43:04', 1);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur_tags`
--

CREATE TABLE `utilisateur_tags` (
  `utilisateur_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `utilisateur_tags`
--

INSERT INTO `utilisateur_tags` (`utilisateur_id`, `tag_id`) VALUES
(1, 22),
(2, 2),
(2, 26),
(2, 28),
(2, 37),
(4, 17),
(5, 2),
(5, 22),
(5, 25);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amis`
--
ALTER TABLE `amis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_amitie` (`utilisateur_id_1`,`utilisateur_id_2`),
  ADD KEY `utilisateur_id_2` (`utilisateur_id_2`),
  ADD KEY `demandeur_id` (`demandeur_id`);

--
-- Indexes for table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organisateur_id` (`organisateur_id`);

--
-- Indexes for table `evenement_participants`
--
ALTER TABLE `evenement_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participation` (`evenement_id`,`utilisateur_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Indexes for table `langues`
--
ALTER TABLE `langues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `destinataire_id` (`destinataire_id`);

--
-- Indexes for table `publications`
--
ALTER TABLE `publications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cle` (`cle`);

--
-- Indexes for table `tags_traductions`
--
ALTER TABLE `tags_traductions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tag_langue` (`tag_id`,`langue_code`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pseudonyme` (`pseudonyme`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `utilisateur_tags`
--
ALTER TABLE `utilisateur_tags`
  ADD PRIMARY KEY (`utilisateur_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amis`
--
ALTER TABLE `amis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `evenement_participants`
--
ALTER TABLE `evenement_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `langues`
--
ALTER TABLE `langues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `publications`
--
ALTER TABLE `publications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
--
-- AUTO_INCREMENT for table `tags_traductions`
--
ALTER TABLE `tags_traductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;
--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `amis`
--
ALTER TABLE `amis`
  ADD CONSTRAINT `amis_ibfk_1` FOREIGN KEY (`utilisateur_id_1`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `amis_ibfk_2` FOREIGN KEY (`utilisateur_id_2`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `amis_ibfk_3` FOREIGN KEY (`demandeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evenements`
--
ALTER TABLE `evenements`
  ADD CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`organisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evenement_participants`
--
ALTER TABLE `evenement_participants`
  ADD CONSTRAINT `evenement_participants_ibfk_1` FOREIGN KEY (`evenement_id`) REFERENCES `evenements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_participants_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `publications`
--
ALTER TABLE `publications`
  ADD CONSTRAINT `publications_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tags_traductions`
--
ALTER TABLE `tags_traductions`
  ADD CONSTRAINT `tags_traductions_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `utilisateur_tags`
--
ALTER TABLE `utilisateur_tags`
  ADD CONSTRAINT `utilisateur_tags_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `utilisateur_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
