<?php
$env = getenv('APPLICATION_ENV') ?: 'prod';

return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
        'Application',
		'User',
    	'Acl',
		'Admin',
    ),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => array(
            './module',
            './vendor',
        ),

        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            sprintf('config/autoload/{,*.}{global,%s,local}.php', $env),
        ),

        // Whether or not to enable a configuration cache.
        // If enabled, the merged configuration will be cached and used in
        // subsequent requests.
        'config_cache_enabled' => ($env == 'prod'),

        // The key used to create the configuration cache file name.
        'config_cache_key' => 'miranda',

        // Whether or not to enable a module class map cache.
        // If enabled, creates a module class map cache which will be used
        // by in future requests, to reduce the autoloading process.
        'module_map_cache_enabled' => ($env == 'prod'),

        // The key used to create the class map cache file name.
        'module_map_cache_key' => 'miranda_module_map',

        // The path in which to cache merged configuration.
        'cache_dir' => 'data/cache',

        // Whether or not to enable modules dependency checking.
        // Enabled by default, prevents usage of modules that depend on other modules
        // that weren't loaded.
        'check_dependencies' => ($env != 'prod'),
    ),

    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
			'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
    		'MirandaDbAdapter' => 'Zend\Db\Adapter\Adapter',
        ),
    ),
);
