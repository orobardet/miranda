<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Acl\Controller\AdminRight' => 'Acl\Controller\AdminRightController',
            'Acl\Controller\AdminRole' => 'Acl\Controller\AdminRoleController',
            'Acl\Controller\Unauthorized' => 'Acl\Controller\UnauthorizedController',
        ),
    ),
		
	'service_manager' => array(
			'aliases' => array(
                    'acl_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
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
    	'template_map' => include __DIR__  .'/../template_map.php',
    ),
);