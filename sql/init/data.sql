-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Ven 18 Octobre 2013 à 21:13
-- Version du serveur: 5.5.32-0ubuntu0.13.04.1
-- Version de PHP: 5.4.9-4ubuntu2.3

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `miranda`
--

--
-- Contenu de la table `rights`
--

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
(12, 'admin_access', 'Admin access', 5, 1),
(13, 'list_costumes', 'List costumes', 6, 1),
(14, 'show_costume', 'Show costume', 6, 2),
(15, 'add_costume', 'Add costume', 6, 3),
(16, 'edit_costume', 'Edit costume', 6, 4),
(17, 'delete_costume', 'Delete costume', 6, 5),
(18, 'admin_costumes', 'Costumes administration', 7, 1),
(19, 'admin_costumes_colors', 'Colors', 7, 2),
(20, 'admin_costumes_materials', 'Materials', 7, 3),
(21, 'admin_costumes_tags', 'Tags', 7, 3),
(22, 'admin_costumes_parts', 'Parts & types', 7, 3);
--
-- Contenu de la table `rights_groups`
--

INSERT INTO `rights_groups` (`id`, `descr`, `ord`) VALUES
(3, 'Admin - Users', 10),
(4, 'Admin - Roles', 11),
(5, 'Common rights', 1),
(6, 'Costumes', 2),
(7, 'Admin - Costumes', 3);

--
-- Contenu de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `descr`) VALUES
(4, 'Administrateur', 'Administrateur général'),
(5, 'Admin utilisateur', 'Ne peut que administrer les utilisateurs, et consulter les droits/rôles'),
(8, 'Invité', ''),
(9, 'Gestionnaire costumes', 'Peut gérer et administrer la base de costumes');

--
-- Contenu de la table `roles_rights`
--

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
(4, 7),
(4, 8),
(4, 9),
(4, 10),
(4, 11),
(4, 12),
(5, 12),
(4, 13),
(9, 13),
(4, 14),
(9, 14),
(4, 15),
(9, 15),
(4, 16),
(9, 16),
(4, 17),
(9, 17),
(4, 18),
(9, 18),
(4, 19),
(9, 19),
(4, 20),
(9, 20),
(4, 21),
(9, 21),
(4, 22),
(9, 22);

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `firstname`, `lastname`, `active`, `creation_ts`, `modification_ts`, `last_login_ts`, `last_activity_ts`) VALUES
(1, 'olivier.robardet@gmail.com', '$2y$10$VJoLooTCnOqnGhscGeVTJu4neP3wrgZt.UUMvnck/QNowxXZKO/sy', 'Olivier', 'Robardet', 1, 1375337740, 1378939873, 1382121802, 1382123395),
(2, 'lodie82@gmail.com', '$2y$10$VJoLooTCnOqnGhscGeVTJu4neP3wrgZt.UUMvnck/QNowxXZKO/sy', 'Elodie', 'Robardet', 1, 1375337740, 1382123392, 1378330578, 1378330623);

--
-- Contenu de la table `users_roles`
--

INSERT INTO `users_roles` (`user_id`, `role_id`) VALUES
(1, 4),
(2, 4),
(2, 5),
(1, 8),
(2, 9);

SET FOREIGN_KEY_CHECKS=1;
