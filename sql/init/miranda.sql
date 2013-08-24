SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


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

INSERT INTO `rights` (`id`, `name`, `descr`, `group_id`, `ord`) VALUES
(1, 'admin_list_users', 'List users', 3, 1),
(2, 'admin_show_user', 'Display user', 3, 2),
(3, 'admin_add_user', 'Add user', 3, 3),
(4, 'admin_edit_user', 'Edit user', 3, 4),
(5, 'admin_delete_user', 'Delete user', 3, 5),
(6, 'admin_list_roles', 'List roles', 4, 2),
(7, 'admin_show_role', 'Display role', 4, 3),
(8, 'admin_add_role', 'Add role', 4, 4),
(9, 'admin_edit_role', 'Edit role', 4, 5),
(10, 'admin_delete_role', 'Delete role', 4, 6),
(11, 'admin_list_rights', 'List rights', 4, 1),
(12, 'admin_access', 'Admin access', 5, 1);

CREATE TABLE IF NOT EXISTS `rights_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du groupe dans la BDD',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description du groupe',
  `ord` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Numéro d''ordre parmi les groupes',
  PRIMARY KEY (`id`),
  KEY `ord` (`ord`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Groupe de droits (sert uniquement pour organiser leur affichage)';

INSERT INTO `rights_groups` (`id`, `descr`, `ord`) VALUES
(3, 'Admin - Users', 10),
(4, 'Admin - Roles', 11),
(5, 'Common rights', 1);

CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du role dans la BDD',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du role (non d''affichage)',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description détaillée du role',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Liste des roles dans la BDD';

INSERT INTO `roles` (`id`, `name`, `descr`) VALUES
(4, 'Administrateur', 'Administrateur général'),
(5, 'Admin utilisateur', 'Ne peut que administrer les utilisateurs, et consulter les droits/rôles');

CREATE TABLE IF NOT EXISTS `roles_rights` (
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'ID du role',
  `right_id` bigint(20) unsigned NOT NULL COMMENT 'ID du droit',
  PRIMARY KEY (`role_id`,`right_id`),
  KEY `right_id` (`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Association des droits par role';

INSERT INTO `roles_rights` (`role_id`, `right_id`) VALUES
(4, 1),
(5, 1),
(4, 2),
(5, 2),
(4, 3),
(5, 3),
(4, 4),
(5, 4),
(4, 5),
(5, 5),
(4, 6),
(5, 6),
(4, 7),
(5, 7),
(4, 8),
(4, 9),
(4, 10),
(4, 11),
(5, 11),
(4, 12),
(5, 12);

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

INSERT INTO `users` (`id`, `email`, `password`, `firstname`, `lastname`, `active`, `creation_ts`, `modification_ts`, `last_login_ts`, `last_activity_ts`) VALUES
(1, 'olivier.robardet@gmail.com', '$2y$10$VJoLooTCnOqnGhscGeVTJu4neP3wrgZt.UUMvnck/QNowxXZKO/sy', 'Olivier', 'Robardet', 1, 1375337740, 1376641195, 1377332701, 1377335725),
(2, 'lodie82@gmail.com', '$2y$10$VJoLooTCnOqnGhscGeVTJu4neP3wrgZt.UUMvnck/QNowxXZKO/sy', 'Elodie', 'Robardet', 0, 1375337740, 1375337740, 0, 0);

CREATE TABLE IF NOT EXISTS `users_roles` (
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'ID de l''utilisateur',
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'ID du role',
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Association des roles par utilisateur';


ALTER TABLE `rights`
  ADD CONSTRAINT `rights_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `rights_groups` (`ID`);

ALTER TABLE `roles_rights`
  ADD CONSTRAINT `roles_rights_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `roles_rights_ibfk_2` FOREIGN KEY (`right_id`) REFERENCES `rights` (`id`);

ALTER TABLE `users_roles`
  ADD CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
