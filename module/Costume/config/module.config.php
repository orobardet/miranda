<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Costume\Controller\Console' => 'Costume\Controller\ConsoleController',
        ),
    ),
	
    'router' => array(
        'routes' => array(
            'costume' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/costume',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Costume\Controller',
                        'controller'    => 'Console',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true
            ),
        ),
    ),
	
	'console' => array(
		'router' => array(
			'routes' => array(
				'costume-import' => array(
					'options' => array(
						'route' => 'import (costume|costumes) <csv_file> [--picture-dir=] [--log-file=] [--error-file=]',
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
				),
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
            __DIR__ . '/../view',
        ),
    ),
);
