<?php
$env = getenv('APPLICATION_ENV') ?: 'prod';

return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
    	'Acl'
    ),

    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor',
        ),

    	'config_glob_paths' => array(
            sprintf('config/autoload/{,*.}{global,%s,local}.php', $env),
        ),

    ),

    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
			'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
    		'Miranda\Service\DbAdapter' => 'Zend\Db\Adapter\Adapter',
        ),
    ),
);
