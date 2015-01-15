#!/usr/bin/env php
<?php
define('CLASSMAP_GENERATOR', 'vendor/bin/classmap_generator.php');
define('TEMPLATEMAP_GENERATOR', 'vendor/bin/templatemap_generator.php');

chdir(__DIR__);

$appConfig = include('config/application.config.php');
$modules = array();
if (!array_key_exists('modules', $appConfig)) {
	throw new Exception("No 'modules' in application config.");
}
$modules = $appConfig['modules'];

if (!count($modules)) {
	echo "No modules.\n";
	exit(0);
}

foreach ($modules as $module) {
	if (is_dir("module/$module")) {
		echo "Generating classmap for module $module...\n";
		system(CLASSMAP_GENERATOR." -w -l module/$module");
		echo "Generating templatemap for module $module...\n";
		system(TEMPLATEMAP_GENERATOR." -w -l module/$module -v module/$module/view");
	}	
}

echo "All maps generated.\n";