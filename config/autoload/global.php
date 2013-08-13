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
$env = getenv('APPLICATION_ENV') ?: 'prod';

return array(
	'application' => array(
		'authentification' => array(
			'not_login_page' => array('login', 'logout', 'authenticate'),
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
    	'charset' => 'UTF-8',
    	'options' => array('buffer_results' => true)
    ),
	'navigation' => array(
			'default' => array(
					array(
							'label' => 'Home',
							'route' => 'home',
					),
					array(
							'label' => 'Admin',
							'route' => 'admin',
							'pages' => array(
									array(
											'label' => 'Users',
											'route' => 'admin/user'
									),
							),
					),
			),
	),
	'session' => array(
			'config' => array(
					'class' => 'Zend\Session\Config\SessionConfig',
					'options' => array(
							'name' => 'miranda',
					),
			),
			'storage' => 'Zend\Session\Storage\SessionArrayStorage',
			'validators' => array(
					'Zend\Session\Validator\RemoteAddr',
					'Zend\Session\Validator\HttpUserAgent',
			),
	),
);
