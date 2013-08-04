<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'db' => array(
        'driver' => 'Mysqli',
        'hostname' => 'localhost',
    	'database' => 'miranda',
    	'charset' => 'UTF-8',
    	'options' => array('buffer_results' => true)
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
			'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
    ),
	'navigation' => array(
			'default' => array(
					array(
							'label' => 'Home',
							'route' => 'home',
					),
					array(
							'label' => 'Admin',
							'route' => 'admin',
							'pages' => array(
									array(
											'label' => 'User',
											'route' => 'admin/user'
									),
							),
					),
			),
	),
);
