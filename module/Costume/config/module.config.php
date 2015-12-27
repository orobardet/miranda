<?php
return [
	'controllers' => [
		'invokables' => [
			'Costume\Controller\Console' => 'Costume\Controller\ConsoleController',
			'Costume\Controller\Costume' => 'Costume\Controller\CostumeController',
			'Costume\Controller\Stats' => 'Costume\Controller\StatsController',
            'Costume\Controller\Admin' => 'Costume\Controller\AdminController',
			'Costume\Controller\AdminColor' => 'Costume\Controller\AdminColorController',
			'Costume\Controller\AdminMaterial' => 'Costume\Controller\AdminMaterialController',
			'Costume\Controller\AdminTag' => 'Costume\Controller\AdminTagController',
			'Costume\Controller\AdminPart' => 'Costume\Controller\AdminPartController'
        ]
    ],
	
	'router' => [
		'routes' => [
			'costume' => [
				'type' => 'segment',
				'options' => [
					'route' => '/costume[/][:action][/:id]',
					'constraints' => [
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+'
                    ],
					'defaults' => [
						'__NAMESPACE__' => 'Costume\Controller',
						'controller' => 'Costume',
						'action' => 'index'
                    ]
                ]
			],
            'costume-stats' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/costume-stats[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Costume\Controller',
                        'controller' => 'Costume\Controller\Stats',
                        'action' => 'index'
                    ]
                ]
            ],
			'costume-admin' => [
				'type' => 'Literal',
				'options' => [
					'route' => '/costume-admin',
					'defaults' => [
						'__NAMESPACE__' => 'Costume\Controller',
						'controller' => 'Admin',
						'action' => 'index'
                    ]
                ],
				'may_terminate' => true,
				'child_routes' => [
					'color' => [
						'type' => 'segment',
						'options' => [
							'route' => '/color[/][:action][/:id]',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
                            ],
							'defaults' => [
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminColor',
								'action' => 'index'
                            ]
                        ]
                    ],
					'material' => [
						'type' => 'segment',
						'options' => [
							'route' => '/material[/][:action][/:id]',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
                            ],
							'defaults' => [
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminMaterial',
								'action' => 'index'
                            ]
                        ]
                    ],
					'tag' => [
						'type' => 'segment',
						'options' => [
							'route' => '/tag[/][:action][/:id]',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
                            ],
							'defaults' => [
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminTag',
								'action' => 'index'
                            ]
                        ]
                    ],
					'part' => [
						'type' => 'segment',
						'options' => [
							'route' => '/part[/][:action][/:id]',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' => '[0-9]+'
                            ],
							'defaults' => [
								'__NAMESPACE__' => 'Costume\Controller',
								'controller' => 'Costume\Controller\AdminPart',
								'action' => 'index'
                            ]
                        ]
                    ]
                ]
            ]
		]
    ],
	
	'console' => [
		'router' => [
			'routes' => [
				'costume-import' => [
					'options' => [
						'route' => 'import (costume|costumes) <csv_file> [--picture-dir=] [--log-file=] [--error-file=] [--tags=]',
						'defaults' => [
							'controller' => 'Costume\Controller\Console',
							'action' => 'import'
                        ]
                    ]
                ],
				'costume-preprare-picture' => [
					'options' => [
						'route' => 'prepare costume (picture|pictures) <input_dir> <output_dir>',
						'defaults' => [
							'controller' => 'Costume\Controller\Console',
							'action' => 'preparepictures'
                        ]
                    ]
                ]
            ]
        ]
    ],
	
	'service_manager' => [
		'aliases' => [
			'costume_zend_db_adapter' => 'Zend\Db\Adapter\Adapter'
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
