<?php
return [
	'controllers' => [
		'invokables' => [
			'User\Controller\Auth' => 'User\Controller\AuthController',
			'User\Controller\Admin' => 'User\Controller\AdminController',
			'User\Controller\Profile' => 'User\Controller\ProfileController',
			'User\Controller\Console' => 'User\Controller\ConsoleController'
		]
	],
	
	'router' => [
		'routes' => [
			'login' => [
				'type' => 'Literal',
				'options' => [
					'route' => '/login',
					'defaults' => [
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'login'
					]
				]
			],
			'authenticate' => [
				'type' => 'Literal',
				'options' => [
					'route' => '/authenticate',
					'defaults' => [
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'authenticate'
					]
				]
			],
			'logout' => [
				'type' => 'Literal',
				'options' => [
					'route' => '/logout',
					'defaults' => [
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'logout'
					]
				]
			],
			'forgot-password' => [
				'type' => 'segment',
				'options' => [
					'route' => '/forgot-password',
					'defaults' => [
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'forgotpassword'
					]
				]
			],
			'reset-password' => [
				'type' => 'segment',
				'options' => [
					'route' => '/reset-password[/:token]',
					'constraints' => [
						'token' => '[a-zA-Z0-9]+'
					],
					'defaults' => [
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'resetpassword'
					]
				]
			],
			'validate-account' => [
				'type' => 'segment',
				'options' => [
					'route' => '/validate-account[/:token]',
					'constraints' => [
						'token' => '[a-zA-Z0-9]+'
					],
					'defaults' => [
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Auth',
						'action' => 'validateaccount'
					]
				]
			],
			'profile' => [
				'type' => 'segment',
				'options' => [
					'route' => '/profile[/][:action]',
					'constraints' => [
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
					],
					'defaults' => [
						'__NAMESPACE__' => 'User\Controller',
						'controller' => 'Profile',
						'action' => 'show'
					]
				]
			]
		]
	],
	
	'console' => [
		'router' => [
			'routes' => [
				'user-list' => [
					'options' => [
						'route' => '(show|list) [all|enabled|disabled]:type users',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'list'
						]
					]
				],
				'user-simple-list' => [
					'options' => [
						'route' => '(show|list) users',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'list'
						]
					]
				],
				'user-search' => [
					'options' => [
						'route' => 'search user <search>',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'search'
						]
					]
				],
				'user-show' => [
					'options' => [
						'route' => 'show user <id>',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'show'
						]
					]
				],
				'user-disable' => [
					'options' => [
						'route' => 'disable user <id>  [-y|--yes]:yes',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'disable'
						]
					]
				],
				'user-enable' => [
					'options' => [
						'route' => 'enable user <id> [-y|--yes]:yes',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'enable'
						]
					]
				],
				'user-change-password' => [
					'options' => [
						'route' => 'change user password <id>',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'changepassword'
						]
					]
				],
				'user-password' => [
					'options' => [
						'route' => 'user password <id>',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'changepassword'
						]
					]
				],
				'add-user' => [
					'options' => [
						'route' => 'add user [<email>] [--firstname=] [--lastname=]',
						'defaults' => [
							'controller' => 'User\Controller\Console',
							'action' => 'adduser'
						]
					]
				]
			]
		]
	],
	
	'service_manager' => [
		'aliases' => [
			'user_zend_db_adapter' => 'Zend\Db\Adapter\Adapter'
		]
	],
	
	'translator' => [
		'locale' => 'fr_FR',
		'translation_file_patterns' => [
			[
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../language',
				'pattern' => '%s.lang.php'
			]
		]
	],
	
	'view_manager' => [
		'template_path_stack' => [
			__DIR__ . '/../view'
		],
		'template_map' => include __DIR__ . '/../template_map.php'
	]
];