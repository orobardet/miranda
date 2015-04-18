DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costume_colors`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costume_colors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID de couleur de costume',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom de la couleur',
  `color` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FFFFFF' COMMENT 'Code hexa de la couleur, comme au format HTML sans le # (#RRVVBB)',
  `ord` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Ordre des couleurs',
  PRIMARY KEY (`id`),
  KEY `ord` (`ord`)
) ENGINE=InnoDB AUTO_INCREMENT=830 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costume_materials`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costume_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID de la matière de costume',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom de la matière',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Matières possible pour un costume'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costume_tags`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costume_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du tag',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du tag',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table contentant la liste des tags associés aux costumes'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costume_types`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costume_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID BDD du type de costume',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du type de costume',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Défini la liste des type de costume, utilisée également pour la composition des '

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costumes`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costumes` (
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
  `origin` enum('creation','purchase','other') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Origine du costume',
  `origin_details` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Détail libre de l''origine du costume (lieu d''achat par exemple)',
  `history` text COLLATE utf8_unicode_ci COMMENT 'Historique d''utilisation du costume',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `genre` (`gender`),
  KEY `primary_color_id` (`primary_color_id`),
  KEY `secondary_color_id` (`secondary_color_id`),
  KEY `primary_material_id` (`primary_material_id`,`secondary_material_id`),
  KEY `secondary_material_id` (`secondary_material_id`),
  KEY `type` (`type_id`),
  KEY `origin` (`origin`),
  KEY `origin_details` (`origin_details`),
  KEY `quantity` (`quantity`),
  KEY `label` (`label`),
  CONSTRAINT `costumes_ibfk_1` FOREIGN KEY (`primary_color_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costume_colors` (`id`),
  CONSTRAINT `costumes_ibfk_2` FOREIGN KEY (`secondary_color_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costume_colors` (`id`),
  CONSTRAINT `costumes_ibfk_3` FOREIGN KEY (`primary_material_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costume_materials` (`id`),
  CONSTRAINT `costumes_ibfk_4` FOREIGN KEY (`secondary_material_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costume_materials` (`id`),
  CONSTRAINT `costumes_ibfk_5` FOREIGN KEY (`type_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costume_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=717 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costumes_pictures`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costumes_pictures` (
  `costume_id` bigint(20) unsigned NOT NULL COMMENT 'ID du costume',
  `picture_id` bigint(2) unsigned NOT NULL COMMENT 'ID de l''image',
  PRIMARY KEY (`costume_id`,`picture_id`),
  KEY `picture_id` (`picture_id`),
  CONSTRAINT `costumes_pictures_ibfk_1` FOREIGN KEY (`costume_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costumes` (`id`),
  CONSTRAINT `costumes_pictures_ibfk_2` FOREIGN KEY (`picture_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%pictures` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costumes_tags`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costumes_tags` (
  `costume_id` bigint(20) unsigned NOT NULL COMMENT 'ID du costume',
  `tag_id` bigint(20) unsigned NOT NULL COMMENT 'ID du tag',
  PRIMARY KEY (`costume_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `costumes_tags_ibfk_1` FOREIGN KEY (`costume_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costumes` (`id`),
  CONSTRAINT `costumes_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costume_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table d''association des costumes aux tags'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%costumes_types`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%costumes_types` (
  `costume_id` bigint(20) unsigned NOT NULL COMMENT 'ID du costume',
  `type_id` bigint(20) unsigned NOT NULL COMMENT 'ID du type',
  PRIMARY KEY (`costume_id`,`type_id`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `costumes_types_ibfk_1` FOREIGN KEY (`costume_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costumes` (`id`),
  CONSTRAINT `costumes_types_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%costume_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table d''association des types aux costumes pour former la composition d''un costu'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%pictures`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%pictures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID image',
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Chemin d''accès de l''image (relative à la racine de stockage des images)',
  `width` int(10) unsigned NOT NULL COMMENT 'Largeur en pixel',
  `height` int(10) unsigned NOT NULL COMMENT 'Hauteur en pixel',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=515 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%rights`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%rights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du droit dans la base de donnée',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du droit, chaine utilisée comme identifiant dans les ACL',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description du droit',
  `group_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID du groupe de droit (s''il y en a un)',
  `ord` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Numéro d''ordre du droit dans le groupe de droit',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `ord` (`ord`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `rights_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%rights_groups` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Droits utilisés pour les ACL'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%rights_groups`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%rights_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du groupe dans la BDD',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description du groupe',
  `ord` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Numéro d''ordre parmi les groupes',
  PRIMARY KEY (`id`),
  KEY `ord` (`ord`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Groupe de droits (sert uniquement pour organiser leur affichage)'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%roles`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID du role dans la BDD',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nom du role (non d''affichage)',
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description détaillée du role',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Liste des roles dans la BDD'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%roles_rights`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%roles_rights` (
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'ID du role',
  `right_id` bigint(20) unsigned NOT NULL COMMENT 'ID du droit',
  PRIMARY KEY (`role_id`,`right_id`),
  KEY `right_id` (`right_id`),
  CONSTRAINT `roles_rights_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%roles` (`id`),
  CONSTRAINT `roles_rights_ibfk_2` FOREIGN KEY (`right_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%rights` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Association des droits par role'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%users`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%users` (
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
  `password_token` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Token de récupération du mot de passe',
  `password_token_ts` int(10) unsigned DEFAULT NULL COMMENT 'Timestamp de date de création du token de récupération du mot de passe',
  `registration_token` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Token de création de compte',
  `registration_token_ts` int(10) unsigned DEFAULT NULL COMMENT 'Timestamp de date de génération du token de création de compte',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `password_token` (`password_token`),
  UNIQUE KEY `registration_token` (`registration_token`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contient les informations sur les comptes utilisateurs'

;;;

DROP TABLE IF EXISTS `%{MIRANDA_TABLE_PREFIX}%users_roles`

;;;

CREATE TABLE `%{MIRANDA_TABLE_PREFIX}%users_roles` (
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'ID de l''utilisateur',
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'ID du role',
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%users` (`id`),
  CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `%{MIRANDA_TABLE_PREFIX}%roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Association des roles par utilisateur'

;;;



