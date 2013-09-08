<?php
/**
 * Display all errors when APPLICATION_ENV is development.
 */
if ($_SERVER['APPLICATION_ENV'] == 'dev') {
	error_reporting(E_ALL | E_STRICT | E_NOTICE | E_DEPRECATED);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
}

/**
 * This makes our life easier when dealing with paths.
 * Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
	return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
try {
	Zend\Mvc\Application::init(require 'config/application.config.php')->run();
} catch (Exception $e) {
	while ($e) {
		echo "<hr/>";
		echo $e->getFile() . ":" . $e->getLine() . "<br/>";
		echo "Exception code '" . $e->getCode() . "' with message: " . $e->getMessage() . "<br/>";
		if (!$e->getPrevious()) {
			$traces = $e->getTrace();
			if (count($traces)) {
				echo "<ul>";
				foreach ($traces as $trace) {
					echo "<li>";
					if (array_key_exists('file', $trace) && array_key_exists('line', $trace)) {
						echo "{$trace['file']}:{$trace['line']} ";
					}
					if (array_key_exists('class', $trace)) {
						echo "{$trace['class']}::";
					}
					if (array_key_exists('function', $trace)) {
						echo "{$trace['function']}";
					}
					echo "</li>";
				}
				echo "</ul>";
			}
		}
		$e = $e->getPrevious();
	}
}
