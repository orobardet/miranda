<?php
namespace Miranda;
/**
 * Ce fichier contient le bootstrap de l'application Miranda.
 * Il doit être inclus dans tous les points d'entrée de l'application, que ce soit des scripts Web ou CLI 
 */
// On défini le fuseau horaire par défaut de l'application
define('REQUEST_MICROTIME', microtime(true));
date_default_timezone_set('Europe/Paris');

$g_bMirandaIsCli = (PHP_SAPI == 'cli');
$g_bMirandaInDev = (getenv('APPLICATION_ENV') == 'dev');

// Si on est en environnement de dev, affichage d'un maximum d'info en cas d'erreur
if ($g_bMirandaInDev || $g_bMirandaIsCli) {
	error_reporting(E_ALL | E_STRICT | E_NOTICE | E_DEPRECATED);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
}

// Pour transformer les messages d'erreurs PHP standards en exception
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
}
\set_error_handler('\Miranda\exception_error_handler');

// On change le répertoire courant pour le parent, qui est la racine des sources du produit
// Ainsi tous les chemins sont relatifs
chdir(__DIR__);

// Initialisation de l'autoloader
require 'init_autoload.php';

// Lancement de l'application
try {
	\Zend\Mvc\Application::init(require 'config/application.config.php')->run();
} catch (\Exception $e) {
	while ($e) {
		echo $g_bMirandaIsCli?"----------------------------".PHP_EOL:"<hr/>";
		echo $e->getFile() . ":" . $e->getLine() . ($g_bMirandaIsCli?PHP_EOL:"<br/>");
		echo "Exception code '" . $e->getCode() . "' with message: " . $e->getMessage() . ($g_bMirandaIsCli?PHP_EOL:"<br/>");
		if (!$e->getPrevious()) {
			$traces = $e->getTrace();
			if (count($traces)) {
                if (!$g_bMirandaIsCli) echo "<ul>";
				foreach ($traces as $trace) {
                    echo $g_bMirandaIsCli?" - ":"<li>";
					if (array_key_exists('file', $trace) && array_key_exists('line', $trace)) {
						echo "{$trace['file']}:{$trace['line']} ";
					}
					if (array_key_exists('class', $trace)) {
						echo "{$trace['class']}::";
					}
					if (array_key_exists('function', $trace)) {
						echo "{$trace['function']}";
					}
					echo $g_bMirandaIsCli?PHP_EOL:"</li>";
				}
                if (!$g_bMirandaIsCli) echo "</ul>";
			}
		}
		$e = $e->getPrevious();
	}
}
