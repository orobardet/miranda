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
								'label' => 'Ajouter',
								'route' => 'admin/user',
								'action' => 'add',
								'resource' => 'admin_add_user',
							),
							array(
								'label' => 'Modifier',
								'route' => 'admin/user',
								'action' => 'edit',
								'resource' => 'admin_edit_user',
							),
							array(
								'label' => 'Supprimer',
								'route' => 'admin/user',
								'action' => 'delete',
								'resource' => 'admin_delete_user',
							)
						)
					),
					array(
						'label' => 'Roles',
						'route' => 'admin/role',
						'resource' => 'admin_list_roles',
						'pages' => array(
							array(
								'label' => 'Ajouter',
								'route' => 'admin/role',
								'action' => 'add',
								'resource' => 'admin_add_role',
							),
							array(
								'label' => 'Modifier',
								'route' => 'admin/role',
								'action' => 'edit',
								'resource' => 'admin_edit_role',
							),
							array(
								'label' => 'Supprimer',
								'route' => 'admin/role',
								'action' => 'delete',
								'resource' => 'admin_delete_role',
							)
						)
					),
					array(
						'label' => 'Rights',
						'route' => 'admin/right',
						'resource' => 'admin_list_rights',
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