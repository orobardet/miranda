<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
	'router' => array(
		'routes' => array(
			'home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/',
					'defaults' => array(
						'controller' => 'Application\Controller\Index',
						'action' => 'index'
					)
				)
			),
			'application' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/application',
					'defaults' => array(
						'__NAMESPACE__' => 'Application\Controller',
						'controller' => 'Index',
						'action' => 'index'
					)
				),
				'may_terminate' => true,
				'child_routes' => array(
					'set-items-per-page' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/setitemsperpage/:context/:items/:redirect',
							'constraints' => array(
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Application\Controller',
								'controller' => 'Application',
								'action' => 'setitemsperpage'
							)
						)
					),
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/[:controller[/:action]]',
							'constraints' => array(
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
							),
							'defaults' => array()
						)
					)
				)
			)
		)
	),
	'service_manager' => array(
		'abstract_factories' => array(
			'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
			'Zend\Log\LoggerAbstractServiceFactory'
		),
		'aliases' => array(
			'app_zend_db_adapter' => 'Zend\Db\Adapter\Adapter'
		)
	),
	'translator' => array(
		'locale' => 'fr_FR',
		'translation_file_patterns' => array(
			array(
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../../../language',
				'pattern' => '%s/Zend_Validate.php'
			),
			array(
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../../../language',
				'pattern' => '%s/Zend_Captcha.php'
			),
			array(
				'type' => 'phparray',
				'base_dir' => __DIR__ . '/../language',
				'pattern' => '%s.lang.php'
			)
		)
	),
	'controllers' => array(
		'invokables' => array(
			'Application\Controller\Index' => 'Application\Controller\IndexController',
			'Application\Controller\Application' => 'Application\Controller\ApplicationController'
		)
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions' => true,
		'doctype' => 'HTML5',
		'not_found_template' => 'error/404',
		'exception_template' => 'error/index',
		'template_map' => array_merge(include __DIR__ . '/../template_map.php', 
				array(
					'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
					'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
					'error/404' => __DIR__ . '/../view/error/404.phtml',
					'error/403' => __DIR__ . '/../view/error/403.phtml',
					'error/index' => __DIR__ . '/../view/error/index.phtml',
					'breadcrumb' => __DIR__ . '/../view/partial/breadcrumb.phtml',
					'paginator/sliding' => __DIR__ . '/../view/partial/paginator-sliding.phtml',
					'table-sorter' => __DIR__ . '/../view/partial/table-sorter.phtml',
					'results-status' => __DIR__ . '/../view/partial/results-status.phtml',
					'result-status' => __DIR__ . '/../view/partial/result-status.phtml'
				)),
		'template_path_stack' => array(
			__DIR__ . '/../view'
		),
		'strategies' => array(
			'ViewJsonStrategy',
		),
	),
	// Placeholder for console routes
	'console' => array(
		'router' => array(
			'routes' => array()
		)
	)
);
