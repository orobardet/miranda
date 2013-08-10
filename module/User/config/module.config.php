<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
        ),
    ),
		
	'router' => array(
 			'routes' => array(
     			    'login' => array(
     			    		'type' => 'Literal',
     			    		'options' => array(
     			    				'route' => '/login',
     			    				'defaults' => array(
     			    						'__NAMESPACE__' => 'User\Controller',
     			    						'controller' => 'User',
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
     			    						'controller' => 'User',
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
     			    						'controller' => 'User',
     			    						'action' => 'logout'
		    						)
		    				)
    	    		),
 			)
	),
		
	'service_manager' => array(
			'aliases' => array(
					'translator' => 'MvcTranslator',
                    'user_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
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