<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
$env = getenv('APPLICATION_ENV') ?  : 'prod';
$app_key = 'application';

$config = array(
	$app_key => array(
		'layout' => array(
			'css' => array(
				'css/jquery-ui-bootstrap/jquery-ui.css',
				'css/bootstrap.css',
				'css/bootstrap-responsive.min.css',
				'css/font-awesome.min.css',
				'css/style.css'
			),
			'compile_less' => false,
			'less_wrapper' => 'less.php',
			'less_compiler' => 'lessphp',
			'less' => array(
				'css/jquery-ui-bootstrap/jquery-ui.css',
				'css/bootstrap.less',
				'css/bootstrap-responsive.min.css',
				'css/font-awesome.min.css',
				'css/style.less'
			),
			'js' => array(
				'js/jquery.min.js',
				'js/json2.js',
				'js/jquery-ui.min.js',
				'js/jquery.loadmask.min.js',
				'js/bootstrap.js',
				'lib/jquery.passstrength.js'
			)
		),
		'authentification' => array(
			'not_login_page' => array(
				'login',
				'logout',
				'authenticate',
				'unauthorized'
			),
			'bcrypt' => array(
				'cost' => 10
			)
		),
		'db' => array(
			'table_prefix' => ''
		),
		'data_storage' => array(
			'root_path' => '../../data/miranda'
		),
		'costume' => array(
			'pictures' => array(
				'max_width' => 1000,
				'max_height' => 1000,
				'store_path' => 'pictures/costumes',
				'url_path' => '/pictures/costumes'
			)
		)
	),
	
	'db' => array(
		'driver' => 'Mysqli',
		'hostname' => 'localhost',
		'database' => 'miranda',
		'charset' => 'UTF8',
		'options' => array(
			'buffer_results' => true
		)
	),
	'navigation' => array(
		'default' => array(
			array(
				'label' => 'Home',
				'route' => 'home'
			),
			array(
				'label' => 'Costumes',
				'route' => 'costume',
				'resource' => 'list_costumes',
				'pages' => array(
					array(
						'label' => 'Catalog',
						'route' => 'costume',
						'action' => 'index',
						'resource' => 'list_costumes',
						'pages' => array(
							array(
								'label' => 'Show',
								'route' => 'costume',
								'action' => 'show',
								'resource' => 'show_costume',
								'visible' => false
							),
							array(
								'label' => 'Add',
								'route' => 'costume',
								'action' => 'add',
								'resource' => 'add_costume',
								'visible' => false
							),
							array(
								'label' => 'Edit',
								'route' => 'costume',
								'action' => 'edit',
								'resource' => 'edit_costume',
								'visible' => false
							),
							array(
								'label' => 'Delete',
								'route' => 'costume',
								'action' => 'delete',
								'resource' => 'delete_costume',
								'visible' => false
							)
						)
					),
					array(
						'label' => 'Management',
						'route' => 'costume-admin',
						'action' => 'index',
						'resource' => 'admin_costumes',
						'pages' => array(
							array(
								'label' => 'Colors',
								'route' => 'costume-admin/color',
								'action' => 'index',
								'resource' => 'admin_costumes_colors'
							),
							array(
								'label' => 'Materials',
								'route' => 'costume-admin/material',
								'action' => 'index',
								'resource' => 'admin_costumes_materials'
							),
							array(
								'label' => 'Tags',
								'route' => 'costume-admin/tag',
								'action' => 'index',
								'resource' => 'admin_costumes_tags'
							),
							array(
								'label' => 'Parts & types',
								'route' => 'costume-admin/part',
								'action' => 'index',
								'resource' => 'admin_costumes_parts'
							)
						)
					)
				)
			),
			array(
				'label' => 'Admin',
				'route' => 'admin',
				'resource' => 'admin_access',
				'pages' => array(
					array(
						'label' => 'Users',
						'route' => 'admin/user',
						'resource' => 'admin_list_users',
						'pages' => array(
							array(
								'label' => 'Show',
								'route' => 'admin/user',
								'action' => 'show',
								'resource' => 'admin_show_user'
							),
							array(
								'label' => 'Add',
								'route' => 'admin/user',
								'action' => 'add',
								'resource' => 'admin_add_user'
							),
							array(
								'label' => 'Edit',
								'route' => 'admin/user',
								'action' => 'edit',
								'resource' => 'admin_edit_user'
							),
							array(
								'label' => 'Delete',
								'route' => 'admin/user',
								'action' => 'delete',
								'resource' => 'admin_delete_user'
							)
						)
					),
					array(
						'label' => 'Roles',
						'route' => 'admin/role',
						'resource' => 'admin_list_roles',
						'pages' => array(
							array(
								'label' => 'Show',
								'route' => 'admin/role',
								'action' => 'show',
								'resource' => 'admin_show_role'
							),
							array(
								'label' => 'Add',
								'route' => 'admin/role',
								'action' => 'add',
								'resource' => 'admin_add_role'
							),
							array(
								'label' => 'Edit',
								'route' => 'admin/role',
								'action' => 'edit',
								'resource' => 'admin_edit_role'
							),
							array(
								'label' => 'Delete',
								'route' => 'admin/role',
								'action' => 'delete',
								'resource' => 'admin_delete_role'
							)
						)
					),
					array(
						'label' => 'Rights',
						'route' => 'admin/right',
						'resource' => 'admin_list_rights'
					)
				)
			)
		)
	),
	'session' => array(
		'config' => array(
			'class' => 'Zend\Session\Config\SessionConfig',
			'options' => array(
				'name' => 'miranda'
			)
		),
		'storage' => 'Zend\Session\Storage\SessionArrayStorage',
		'validators' => array(
			'Zend\Session\Validator\RemoteAddr',
			'Zend\Session\Validator\HttpUserAgent'
		)
	)
);

if ($env == 'prod') {
	$config[$app_key]['layout']['css'] = array(
		'css/miranda-full-min.css'
	);
	$config[$app_key]['layout']['js'] = array(
		'js/miranda-full-min.js'
	);
}

return $config;