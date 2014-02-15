<?php
/**
 * Script de suivi de la progression d'un upload.
 * 
 * A appeller en pooling (en ajax depuis un javascript par exemple) en passant en GET le paramètre 'id' contenant l'ID de suivi de progression 
 * d'upload.
 * 
 * Pas d'autres paramètres reconnus.
 * 
 * N'utilise pas le bootstrap de l'application pour des raisons de performance, ne charge que ce qui est nécessaire.
 * 
 * Retourne le JSON suivant (exemple)
 * {
 * 	"start_time":1392267009,
 * 	"content_length":6304744,
 * 	"bytes_processed":5248,
 * 	"done":false,
 * 	"files": [{
 * 			"field_name":"picture_file",
 * 			"name":"DSC_0001.JPG",
 * 			"tmp_name":null,
 * 			"error":0,
 * 			"done":false,
 * 			"start_time":1392267009,
 * 			"bytes_processed":0
 * 		}],
 * 	"total":6304744,
 * 	"current":5248,
 * 	"rate":5248,
 * 	"message":"5.13kB - 6.01MB",
 * 	"id":"52fc4ef9c1103"
 * }
 */

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
	return false;
}

// Nécessaire pour pouvoir lire la progression de l'upload dans la session
session_start();

// On défini le fuseau horaire par défaut de l'application
date_default_timezone_set('Europe/Paris');

// Si on est en environnement de dev, affichage d'un maximum d'info en cas d'erreur
if (getenv('APPLICATION_ENV') == 'dev') {
	error_reporting(E_ALL | E_STRICT | E_NOTICE | E_DEPRECATED);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
}

// On change le répertoire courant pour le parent, qui est la racine des sources du produit
// Ainsi tous les chemins sont relatifs
chdir(dirname(__DIR__));

require 'init_autoload.php';

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
header("Pragma: no-cache");

$request = new Zend\Http\PhpEnvironment\Request();
$id = $request->getQuery('id', null);
$progress = new \Zend\ProgressBar\Upload\SessionProgress();
$model = new \Zend\View\Model\JsonModel($progress->getProgress($id));

echo $model->serialize();
