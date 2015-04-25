INSERT INTO `%{MIRANDA_TABLE_PREFIX}%rights` (`id`, `name`, `descr`, `group_id`, `ord`) VALUES
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
(22, 'admin_costumes_tags', 'Tags', 7, 4),
(23, 'admin_costumes_parts', 'Parts & types', 7, 5),
(24, 'admin_update_acl', 'Update ACL', 4, 7)

;;;

INSERT INTO `%{MIRANDA_TABLE_PREFIX}%rights_groups` (`id`, `descr`, `ord`) VALUES
(3, 'Admin - Users', 10),
(4, 'Admin - Roles', 11),
(5, 'Common rights', 1),
(6, 'Costumes', 2),
(7, 'Admin - Costumes', 3)

;;;

INSERT INTO `%{MIRANDA_TABLE_PREFIX}%roles` (`id`, `name`, `descr`) VALUES
(1, 'Administrateur', 'Administrateur général');
;;;

INSERT INTO `%{MIRANDA_TABLE_PREFIX}%roles_rights` (`role_id`, `right_id`) VALUES
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
(9, 13),
(9, 14),
(9, 15),
(9, 16),
(9, 17),
(9, 18),
(9, 19),
(9, 20),
(9, 22),
(9, 23),
(4, 24)