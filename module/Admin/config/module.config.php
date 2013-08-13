<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\User' => 'Admin\Controller\UserController',
        ),
    ),
		
	'router' => array(
 			'routes' => array(
     			    'admin' => array(
     			    		'type' => 'Literal',
     			    		'options' => array(
     			    				'route' => '/admin',
     			    				'defaults' => array(
     			    						'__NAMESPACE__' => 'Admin\Controller',
     			    						'controller' => 'Index',
     			    						'action' => 'index'
		    						)
		    				),
     			    		'may_terminate' => true,
     			    		'child_routes' => array(
         			    		    'user' => array(
         			    		    		'type' => 'segment',
         			    		    		'options' => array(
         			    		    				'route' => '/user[/][:action][/:id]',
                									'constraints' => array(
                											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                											'id'     => '[0-9]+',
                									),
                									'defaults' => array(
                											'controller' => 'Admin\Controller\User',
                											'action'     => 'index',
                									),
 			    		    				),
 			    		    		)
		    				)
    	    		),
 			),
	),
		
	'translator' => array(
			'locale' => 'fr_FR',
			'translation_file_patterns' => array(
					array(
							'type'     => 'phparray',
							'base_dir' => __DIR__ . '/../language',
							'pattern'  => '%s.lang.php',
					),
			),
	),
		
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);