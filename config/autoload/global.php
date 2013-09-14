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
				'css/bootstrap.css',
				'css/style.css',
				'css/bootstrap-responsive.min.css'
			),
			'compile_less' => ($env == 'dev'),
			'less_compiler' => 'less.php',
			'less' => array(
				'css/bootstrap.less',
				'css/style.less',
				'css/bootstrap-responsive.min.css'
			),
			'js' => array(
				'js/jquery.min.js',
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
		'data' => array(
			'root_path' => '/home/orobardet/data/miranda'
		),
		'costume' => array(
			'pictures' => array(
				'max_width' => 1000,
				'max_height' => 1000,
				'store_path' => 'costumes/pictures'
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
						'label' => 'Costumes',
						'route' => 'costume',
						'action' => 'index',
						'resource' => 'list_costumes'
				),
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