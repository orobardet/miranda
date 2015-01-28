<?php
/**
 * Zend Framework (http://framework.zend.com/]
 *
 * @link http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c] 2005-2013 Zend Technologies USA Inc. (http://www.zend.com]
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
return [
	'router' => [
		'routes' => [
			'home' => [
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => [
					'route' => '/',
					'defaults' => [
						'controller' => 'Application\Controller\Index',
						'action' => 'index'
					]
				]
			],
			'application' => [
				'type' => 'Literal',
				'options' => [
					'route' => '/application',
					'defaults' => [
						'__NAMESPACE__' => 'Application\Controller',
						'controller' => 'Index',
						'action' => 'index'
					]
				],
				'may_terminate' => true,
				'child_routes' => [
					'set-items-per-page' => [
						'type' => 'segment',
						'options' => [
							'route' => '/setitemsperpage/:context/:items/:redirect',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
							],
							'defaults' => [
								'__NAMESPACE__' => 'Application\Controller',
								'controller' => 'Application',
								'action' => 'setitemsperpage'
							]
						]
					],
					'default' => [
						'type' => 'Segment',
						'options' => [
							'route' => '/[:controller[/:action]]',
							'constraints' => [
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
							],
							'defaults' => []
						]
					]
				]
			]
		]
	],
	
	'console' => [
		'router' => [
			'routes' => [
				'send-test-email' => [
					'options' => [
						'route' => 'send test email to <email>',
						'defaults' => [
							'controller' => 'Application\Controller\Console',
							'action' => 'testemail'
						]
					]
				],
				'clean-app-cache' => [
					'options' => [
						'route' => 'clean app cache',
						'defaults' => [
							'controller' => 'Application\Controller\Console',
							'action' => 'cleanappcache'
						]
					]
				]
			]
		]
	],
	
	'service_manager' => [
		'abstract_factories' => [
			'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
			'Zend\Log\LoggerAbstractServiceFactory'
		],
		'aliases' => [
			'app_zend_db_adapter' => 'Zend\Db\Adapter\Adapter'
		]
	],
	
	'translator' => [
		'locale' => 'fr_FR',
		'translation_file_patterns' => [
			[
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../../../language',
				'pattern' => '%s/Zend_Validate.php'
			],
			[
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../../../language',
				'pattern' => '%s/Zend_Captcha.php'
			],
			[
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../language',
				'pattern' => '%s.lang.php'
			]
		]
	],
	
	'controllers' => [
		'invokables' => [
			'Application\Controller\Index' => 'Application\Controller\IndexController',
			'Application\Controller\Application' => 'Application\Controller\ApplicationController',
			'Application\Controller\Console' => 'Application\Controller\ConsoleController'
		]
	],
	
	'view_manager' => [
		'display_not_found_reason' => true,
		'display_exceptions' => true,
		'doctype' => 'HTML5',
		'not_found_template' => 'error/404',
		'exception_template' => 'error/index',
		'template_map' => array_merge(include __DIR__ . '/../template_map.php', 
				[
					'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
					'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
					'error/404' => __DIR__ . '/../view/error/404.phtml',
					'error/403' => __DIR__ . '/../view/error/403.phtml',
					'error/index' => __DIR__ . '/../view/error/index.phtml',
					'breadcrumb' => __DIR__ . '/../view/partial/breadcrumb.phtml',
					'paginator/sliding' => __DIR__ . '/../view/partial/paginator-sliding.phtml',
					'table-sorter' => __DIR__ . '/../view/partial/table-sorter.phtml',
					'results-status' => __DIR__ . '/../view/partial/results-status.phtml',
					'result-status' => __DIR__ . '/../view/partial/result-status.phtml',
					'email/layout' => __DIR__ . '/../view/email/layout.phtml'
				]),
		'template_path_stack' => [
			__DIR__ . '/../view'
		],
		'strategies' => [
			'ViewJsonStrategy'
		]
	]
];
