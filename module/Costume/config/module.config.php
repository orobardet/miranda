<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'Costume\Controller\Console' => 'Costume\Controller\ConsoleController',
			'Costume\Controller\Costume' => 'Costume\Controller\CostumeController',
			'Costume\Controller\Admin' => 'Costume\Controller\AdminController',
			'Costume\Controller\AdminColor' => 'Costume\Controller\AdminColorController',
			'Costume\Controller\AdminMaterial' => 'Costume\Controller\AdminMaterialController',
			'Costume\Controller\AdminTag' => 'Costume\Controller\AdminTagController',
			'Costume\Controller\AdminPart' => 'Costume\Controller\AdminPartController'
		)
	),
	
	'router' => array(
		'routes' => array(
			'costume' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/costume[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Costume\Controller',
						'controller' => 'Costume',
						'action' => 'index'
					)
				)
			),
			'costume-admin' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/costume-admin',
					'defaults' => array(
						'__NAMESPACE__' => 'Costume\Controller',
						'controller' => 'Admin',
						'action' => 'index'
					)
				),
				'may_terminate' => true,
				'child_routes' => array(
					'color' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/color[/][:action][/:id]',
							'constraints' => array(
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminColor',
								'action' => 'index'
							)
						)
					),
					'material' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/material[/][:action][/:id]',
							'constraints' => array(
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminMaterial',
								'action' => 'index'
							)
						)
					),
					'tag' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/tag[/][:action][/:id]',
							'constraints' => array(
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminTag',
								'action' => 'index'
							)
						)
					),
					'part' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/part[/][:action][/:id]',
							'constraints' => array(
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminPart',
								'action' => 'index'
							)
						)
					)
				)
			)
		)
	),
	
	'console' => array(
		'router' => array(
			'routes' => array(
				'costume-import' => array(
					'options' => array(
						'route' => 'import (costume|costumes) <csv_file> [--picture-dir=] [--log-file=] [--error-file=] [--tags=]',
						'defaults' => array(
							'controller' => 'Costume\Controller\Console',
							'action' => 'import'
						)
					)
				),
				'costume-preprare-picture' => array(
					'options' => array(
						'route' => 'prepare costume (picture|pictures) <input_dir> <output_dir>',
						'defaults' => array(
							'controller' => 'Costume\Controller\Console',
							'action' => 'preparepictures'
						)
					)
				)
			)
		)
	),
	
	'service_manager' => array(
		'aliases' => array(
			'costume_zend_db_adapter' => 'Zend\Db\Adapter\Adapter'
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
		'template_map' => include __DIR__ . '/../template_map.php'
	)
);
