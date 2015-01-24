<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'User\Controller\Auth' => 'User\Controller\AuthController',
			'User\Controller\Admin' => 'User\Controller\AdminController',
			'User\Controller\Profile' => 'User\Controller\ProfileController',
			'User\Controller\Console' => 'User\Controller\ConsoleController'
		)
	),
	
	'router' => array(
		'routes' => array(
			'login' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/login',
					'defaults' => array(
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'login'
					)
				)
			),
			'authenticate' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/authenticate',
					'defaults' => array(
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'authenticate'
					)
				)
			),
			'logout' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/logout',
					'defaults' => array(
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'logout'
					)
				)
			),
			'profile' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/profile[/][:action]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Profile',
						'action' => 'show'
					)
				)
			)
		)
	),
	
	'console' => array(
		'router' => array(
			'routes' => array(
				'user-list' => array(
					'options' => array(
						'route' => '(show|list) [all|enabled|disabled]:type users',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'list'
						)
					)
				),
				'user-simple-list' => array(
					'options' => array(
						'route' => '(show|list) users',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'list'
						)
					)
				),
				'user-search' => array(
					'options' => array(
						'route' => 'search user <search>',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'search'
						)
					)
				),
				'user-show' => array(
					'options' => array(
						'route' => 'show user <id>',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'show'
						)
					)
				),
				'user-disable' => array(
					'options' => array(
						'route' => 'disable user <id>  [-y|--yes]:yes',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'disable'
						)
					)
				),
				'user-enable' => array(
					'options' => array(
						'route' => 'enable user <id> [-y|--yes]:yes',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'enable'
						)
					)
				),
				'user-change-password' => array(
					'options' => array(
						'route' => 'change user password <id>',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'changepassword'
						)
					)
				),
				'user-password' => array(
					'options' => array(
						'route' => 'user password <id>',
						'defaults' => array(
							'controller' => 'User\Controller\Console',
							'action' => 'changepassword'
						)
					)
				)
			)
		)
	),
	
	'service_manager' => array(
		'aliases' => array(
			'user_zend_db_adapter' => 'Zend\Db\Adapter\Adapter'
		)
	),
	
	'translator' => array(
		'locale' => 'fr_FR',
		'translation_file_patterns' => array(
			array(
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../language',
				'pattern' => '%s.lang.php'
			)
		)
	),
	
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view'
		),
		'template_map' => include __DIR__  .'/../template_map.php',
	)
);