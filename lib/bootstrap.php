<?php
/**
 * Ce fichier contient le bootstrap de l'application Miranda.
 * Il doit être inclus dans tous les points d'entrée de l'application, que ce soit des scripts Web ou CLI 
 */

// Si on est en environnement de dev, affichage d'un maximum d'info en cas d'erreur
if (getenv('APPLICATION_ENV') == 'dev') {
	error_reporting(E_ALL | E_STRICT | E_NOTICE | E_DEPRECATED);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
}

// On change le répertoire courant pour le parent, qui est la racine des sources du produit
// Ainsi tous les chemins sont relatifs
chdir(dirname(__DIR__));

// Initialisation de l'autoloader
require 'init_autoloader.php';

// Lancement de l'application
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
