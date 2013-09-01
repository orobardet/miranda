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
				'pages' => array(
					array(
						'label' => 'Users',
						'route' => 'admin/user',
						'pages' => array(
							array(
								'label' => 'Ajouter',
								'route' => 'admin/user',
								'action' => 'add'
							),
							array(
								'label' => 'Modifier',
								'route' => 'admin/user',
								'action' => 'edit'
							),
							array(
								'label' => 'Supprimer',
								'route' => 'admin/user',
								'action' => 'delete'
							)
						)
					),
					array(
						'label' => 'Roles',
						'route' => 'admin/role',
						'pages' => array(
							array(
								'label' => 'Ajouter',
								'route' => 'admin/role',
								'action' => 'add'
							),
							array(
								'label' => 'Modifier',
								'route' => 'admin/role',
								'action' => 'edit'
							),
							array(
								'label' => 'Supprimer',
								'route' => 'admin/role',
								'action' => 'delete'
							)
						)
					),
					array(
						'label' => 'Rights',
						'route' => 'admin/right',
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