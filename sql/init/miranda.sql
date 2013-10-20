-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Dim 20 Octobre 2013 à 23:57
-- Version du serveur: 5.5.32-0ubuntu0.13.04.1
-- Version de PHP: 5.4.9-4ubuntu2.3

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `miranda`
--

-- --------------------------------------------------------

--
-- Structure de la table `costumes`
--

DROP TABLE IF EXISTS `costumes`;
CREATE TABLE IF NOT EXISTS `costumes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID interne du costume',
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Code référence texte du costume',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom ou libellé court du costume',
  `creation_ts` int(10) unsigned NOT NULL COMMENT 'Date de création (timestamp)',
  `modification_ts` int(10) unsigned NOT NULL COMMENT 'Date de modification (timestamp)',
  `descr` text COLLATE utf8_unicode_ci COMMENT 'Description détaillée du costume',
  `gender` enum('Homme','Femme','Mixte') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Genre (homme, femme, mixte), s''il y en a un',
  `size` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Taille du costume : U (unique), S, M, L, XL, Enfant, ou une taille numérique',
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Etat du costume',
  `quantity` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Quantité du même costume',
  `primary_color_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID de la couleur principale',
  `secondary_color_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID de la couleur secondaire',
  `primary_material_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID de la matière principale du costume',
  `secondary_material_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID de la matière secondaire du costume',
  `type_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID du type (principal) d''un costume',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `genre` (`gender`),
  KEY `primary_color_id` (`primary_color_id`),
  KEY `secondary_color_id` (`secondary_color_id`),
  KEY `primary_material_id` (`primary_material_id`,`secondary_material_id`),
  KEY `secondary_material_id` (`secondary_material_id`),
  KEY `type` (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `costumes_pictures`
--

DROP TABLE IF EXISTS `costumes_pictures`;
CREATE TABLE IF NOT EXISTS `costumes_pictures` (
  `costume_id` bigint(20) unsigned NOT NULL COMMENT 'ID du costume',
  `picture_id` bigint(2) unsigned NOT NULL COMMENT 'ID de l''image',
  PRIMARY KEY (`costume_id`,`picture_id`),
  KEY `picture_id` (`picture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `costumes_tags`
--

DROP TABLE IF EXISTS `costumes_tags`;
CREATE TABLE IF NOT EXISTS `costumes_tags` (
  `costume_id` bigint(20) unsigned NOT NULL COMMENT 'ID du costume',
  `tag_id` bigint(20) unsigned NOT NULL COMMENT 'ID du tag',
  PRIMARY KEY (`costume_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table d''association des costumes aux tags';

-- --------------------------------------------------------

--
-- Structure de la table `costumes_types`
--

DROP TABLE IF EXISTS `costumes_types`;
CREATE TABLE IF NOT EXISTS `costumes_types` (
  `costume_id` bigint(20) unsigned NOT NULL COMMENT 'ID du costume',
  `type_id` bigint(20) unsigned NOT NULL COMMENT 'ID du type',
  PRIMARY KEY (`costume_id`,`type_id`),
  KEY `type_id` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table d''association des types aux costumes pour former la composition d''un costu';

-- --------------------------------------------------------

--
-- Structure de la table `costume_colors`
--

DROP TABLE IF EXISTS `costume_colors`;
CREATE TABLE IF NOT EXISTS `costume_colors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID de couleur de costume',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom de la couleur',
  `color` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FFFFFF' COMMENT 'Code hexa de la couleur, comme au format HTML sans le # (#RRVVBB)',
  `ord` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Ordre des couleurs',
  PRIMARY KEY (`id`),
  KEY `ord` (`ord`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `costume_materials`
--

DROP TABLE IF EXISTS `costume_materials`;
CREATE TABLE IF NOT EXISTS `costume_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID de la matière de costume',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom de la matière',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Matières possible pour un costume';

-- --------------------------------------------------------

--
-- Structure de la table `costume_tags`
--

DROP TABLE IF EXISTS `costume_tags`;
CREATE TABLE IF NOT EXISTS `costume_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du tag',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du tag',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table contentant la liste des tags associés aux costumes';

-- --------------------------------------------------------

--
-- Structure de la table `costume_types`
--

DROP TABLE IF EXISTS `costume_types`;
CREATE TABLE IF NOT EXISTS `costume_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID BDD du type de costume',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du type de costume',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Défini la liste des type de costume, utilisée également pour la composition des ';

-- --------------------------------------------------------

--
-- Structure de la table `pictures`
--

DROP TABLE IF EXISTS `pictures`;
CREATE TABLE IF NOT EXISTS `pictures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID image',
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Chemin d''accès de l''image (relative à la racine de stockage des images)',
  `width` int(10) unsigned NOT NULL COMMENT 'Largeur en pixel',
  `height` int(10) unsigned NOT NULL COMMENT 'Hauteur en pixel',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rights`
--

DROP TABLE IF EXISTS `rights`;
CREATE TABLE IF NOT EXISTS `rights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du droit dans la base de donnée',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du droit, chaine utilisée comme identifiant dans les ACL',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description du droit',
  `group_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID du groupe de droit (s''il y en a un)',
  `ord` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Numéro d''ordre du droit dans le groupe de droit',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `ord` (`ord`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Droits utilisés pour les ACL';

-- --------------------------------------------------------

--
-- Structure de la table `rights_groups`
--

DROP TABLE IF EXISTS `rights_groups`;
CREATE TABLE IF NOT EXISTS `rights_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du groupe dans la BDD',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description du groupe',
  `ord` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Numéro d''ordre parmi les groupes',
  PRIMARY KEY (`id`),
  KEY `ord` (`ord`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Groupe de droits (sert uniquement pour organiser leur affichage)';

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du role dans la BDD',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du role (non d''affichage)',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description détaillée du role',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Liste des roles dans la BDD';

-- --------------------------------------------------------

--
-- Structure de la table `roles_rights`
--

DROP TABLE IF EXISTS `roles_rights`;
CREATE TABLE IF NOT EXISTS `roles_rights` (
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'ID du role',
  `right_id` bigint(20) unsigned NOT NULL COMMENT 'ID du droit',
  PRIMARY KEY (`role_id`,`right_id`),
  KEY `right_id` (`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Association des droits par role';

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID utilisateur',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Adresse email, sert de login',
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Mot de passe crypté et salé (BCrypt)',
  `firstname` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Prénom',
  `lastname` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 si le compte est activé, 0 sinon',
  `creation_ts` int(10) unsigned NOT NULL COMMENT 'Date de création (timestamp)',
  `modification_ts` int(10) unsigned NOT NULL COMMENT 'Date de modification (timestamp)',
  `last_login_ts` int(10) unsigned NOT NULL COMMENT 'Date de dernière connexion (timestamp)',
  `last_activity_ts` int(10) unsigned NOT NULL COMMENT 'Date de dernière activité sur le site (timestamp)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contient les informations sur les comptes utilisateurs';

-- --------------------------------------------------------

--
-- Structure de la table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
CREATE TABLE IF NOT EXISTS `users_roles` (
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'ID de l''utilisateur',
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'ID du role',
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Association des roles par utilisateur';

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `costumes`
--
ALTER TABLE `costumes`
  ADD CONSTRAINT `costumes_ibfk_5` FOREIGN KEY (`type_id`) REFERENCES `costume_types` (`id`),
  ADD CONSTRAINT `costumes_ibfk_1` FOREIGN KEY (`primary_color_id`) REFERENCES `costume_colors` (`id`),
  ADD CONSTRAINT `costumes_ibfk_2` FOREIGN KEY (`secondary_color_id`) REFERENCES `costume_colors` (`id`),
  ADD CONSTRAINT `costumes_ibfk_3` FOREIGN KEY (`primary_material_id`) REFERENCES `costume_materials` (`id`),
  ADD CONSTRAINT `costumes_ibfk_4` FOREIGN KEY (`secondary_material_id`) REFERENCES `costume_materials` (`id`);

--
-- Contraintes pour la table `costumes_pictures`
--
ALTER TABLE `costumes_pictures`
  ADD CONSTRAINT `costumes_pictures_ibfk_1` FOREIGN KEY (`costume_id`) REFERENCES `costumes` (`id`),
  ADD CONSTRAINT `costumes_pictures_ibfk_2` FOREIGN KEY (`picture_id`) REFERENCES `pictures` (`id`);

--
-- Contraintes pour la table `costumes_tags`
--
ALTER TABLE `costumes_tags`
  ADD CONSTRAINT `costumes_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `costume_tags` (`id`),
  ADD CONSTRAINT `costumes_tags_ibfk_1` FOREIGN KEY (`costume_id`) REFERENCES `costumes` (`id`);

--
-- Contraintes pour la table `costumes_types`
--
ALTER TABLE `costumes_types`
  ADD CONSTRAINT `costumes_types_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `costume_types` (`id`),
  ADD CONSTRAINT `costumes_types_ibfk_1` FOREIGN KEY (`costume_id`) REFERENCES `costumes` (`id`);

--
-- Contraintes pour la table `rights`
--
ALTER TABLE `rights`
  ADD CONSTRAINT `rights_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `rights_groups` (`ID`);

--
-- Contraintes pour la table `roles_rights`
--
ALTER TABLE `roles_rights`
  ADD CONSTRAINT `roles_rights_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `roles_rights_ibfk_2` FOREIGN KEY (`right_id`) REFERENCES `rights` (`id`);

--
-- Contraintes pour la table `users_roles`
--
ALTER TABLE `users_roles`
  ADD CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
SET FOREIGN_KEY_CHECKS=1;
