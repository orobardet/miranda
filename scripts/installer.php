#!/usr/bin/env php
<?php
namespace Miranda\Installer;

if (php_sapi_name() !== 'cli') {
	echo "\033[31m*** Must be run on commande line.\033[0m\n";
	exit(1);
}
chdir(dirname(__DIR__));
//chdir('/home/orobardet/www/miranda2');

// On défini le fuseau horaire par défaut de l'application
date_default_timezone_set('Europe/Paris');

// Affichage d'un maximum d'info en cas d'erreur
error_reporting(E_ALL | E_STRICT | E_NOTICE | E_DEPRECATED);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);

// Pour transformer les messages d'erreurs PHP standards en exception
function exception_error_handler($errno, $errstr, $errfile, $errline)
{
	throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("\\Miranda\\Installer\\exception_error_handler");

// Initialisation autoloader
require_once 'vendor/zendframework/zendframework/library/Zend/Loader/AutoloaderFactory.php';
require_once 'vendor/zendframework/zendframework/library/Zend/Loader/ClassMapAutoloader.php';
\Zend\Loader\AutoloaderFactory::factory(
		array(
			'Zend\Loader\ClassMapAutoloader' => array(
				'Composer' => 'vendor/composer/autoload_classmap.php',
				'Application' => 'module/Application/autoload_classmap.php'
			),
			'Zend\Loader\StandardAutoloader' => array(
				'autoregister_zf' => true
			)
		));

use Zend\Console\Console;
use Zend\Console\ColorInterface as ConsoleColorInterface;
use Zend\Console\Prompt\Confirm as ConsoleConfirm;
use Zend\Console\Prompt\Line as ConsoleInput;
use Application\Console\Prompt\Password as ConsolePassword;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Uri\Http as HttpUri;

function exitWithError($msg, $code)
{
	$console = Console::getInstance();
	$console->writeLine("\n*** $msg\n", ConsoleColorInterface::RED);
	exit($code);
}

function createDirectory($path, $mask, $recursive = false)
{
	if (!file_exists($path)) {
		try {
			if (!mkdir($path, null, $recursive)) {
				exitWithError("Error while creation '$path' directory.", 1);
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
			exitWithError("Error while creation '$path' directory.", 1);
		}
	} elseif (!is_dir($path)) {
		exitWithError("'$path' already exists and is not a directory.", 1);
	} elseif (!is_writable($path)) {
		exitWithError("'$path' directory already exists but is not a writable.", 1);
	}
	chmod($path, $mask);
}

function loadSqlFile($db, $sqlFile, $tablePrefix)
{
	if (!file_exists($sqlFile)) {
		exitWithError("SQL file $sqlFile does not exists.", 2);
	}
	
	if (!is_file($sqlFile) || !is_readable($sqlFile)) {
		exitWithError("SQL file $sqlFile is not a regular readable file.", 2);
	}
	
	$sqlContent = file_get_contents($sqlFile);
	$queries = array_filter(array_map('trim', preg_split('/\n;;;\n/', $sqlContent)), 
			function ($value)
			{
				if (!$value || $value === '') {
					return false;
				}
				
				return true;
			});
	
	if (count($queries)) {
		if (!$db->query("SET FOREIGN_KEY_CHECKS=0;", DbAdapter::QUERY_MODE_EXECUTE)) {
			throw new \Exception("Can't disable foreign key checks.");
		}
		foreach ($queries as $query) {
			$query = str_replace('%{MIRANDA_TABLE_PREFIX}%', $tablePrefix, $query);
			if (trim($query) !== '') {
				if (!$db->query($query, DbAdapter::QUERY_MODE_EXECUTE)) {
					throw new \Exception("Can't create a table.");
				}
			}
		}
		if (!$db->query("SET FOREIGN_KEY_CHECKS=1;", DbAdapter::QUERY_MODE_EXECUTE)) {
			throw new \Exception("Can't disable foreign key checks.");
		}
	}
	
	return true;
}

$console = Console::getInstance();
// Création data/cache dir
$console->write('Creating cache directory data/cache... ');
createDirectory('data', 0775);
createDirectory('data/cache', 0777);

$console->writeLine('OK!', ConsoleColorInterface::GREEN);
$console->writeLine('Note: this directory MUST be accessible and writable by the websever.', ConsoleColorInterface::YELLOW);

$console->writeLine("\nI'll now configure your system. You must have the following information:");
$console->writeLine(" - Hostname and port of the MySQL database");
$console->writeLine(" - Name of the MySQL database, which must already exists");
$console->writeLine(" - Username and password of the MySQL account with all grant to the database");
$console->writeLine(" - The base URL that will be used to access your new installation");
$console->writeLine('Note: all the following parameters can be easly changed latter by editing the generated configuration file.', 
		ConsoleColorInterface::YELLOW);

if (!ConsoleConfirm::prompt("\n" . $console->colorize("Do you have all these information?", ConsoleColorInterface::LIGHT_WHITE) . " [y/n] ")) {
	$console->writeLine("\nPlease gather all the needed information and relaunch this installer.\nSee you soon!", ConsoleColorInterface::YELLOW);
	exit(0);
}

// Création BDD (demande conf accès puis création schéma)
$dbConfig = [
	'driver' => 'Mysqli',
	'hostname' => 'localhost',
	'port' => '3306',
	'database' => '',
	'username' => '',
	'password' => '',
	'charset' => 'UTF8',
	'table_prefix' => ''
];

$console->writeLine("\nDatabase configuration:", ConsoleColorInterface::CYAN);
$dbConfig['hostname'] = trim(ConsoleInput::prompt($console->colorize("MySQL server host [localhost]: ", ConsoleColorInterface::LIGHT_WHITE), true));
if ($dbConfig['hostname'] == '') {
	$dbConfig['hostname'] = 'localhost';
}
$dbConfig['port'] = (int)trim(ConsoleInput::prompt($console->colorize("MySQL server port [3306]: ", ConsoleColorInterface::LIGHT_WHITE), true));
if (($dbConfig['port'] <= 0) || ($dbConfig['port'] > 65535)) {
	$dbConfig['port'] = 3306;
}
$dbConfig['database'] = trim(ConsoleInput::prompt($console->colorize("Database name: ", ConsoleColorInterface::LIGHT_WHITE), false));
$dbConfig['username'] = trim(ConsoleInput::prompt($console->colorize("Database username: ", ConsoleColorInterface::LIGHT_WHITE), false));
$dbConfig['password'] = trim(ConsolePassword::prompt($console->colorize("Database password: ", ConsoleColorInterface::LIGHT_WHITE), false));

$console->write(
		"Trying to connect to mysql://" . $dbConfig['username'] . ":***@" . $dbConfig['hostname'] . ":" . $dbConfig['port'] . "/" .
				 $dbConfig['database'] . "... ");

try {
	$db = new DbAdapter($dbConfig);
	$db->query("SELECT 1;");
} catch (\Exception $e) {
	$console->writeLine("KO!", ConsoleColorInterface::RED);
	exitWithError($e->getMessage(), 1);
}
$console->writeLine("OK!", ConsoleColorInterface::GREEN);
$dbConfig['table_prefix'] = trim(
		ConsoleInput::prompt($console->colorize("[OPTIONAL] Database table prefix []: ", ConsoleColorInterface::LIGHT_WHITE), true));

try {
	$console->write('Creating database structure... ');
	if (!loadSqlFile($db, 'sql/init/miranda.sql', $dbConfig['table_prefix'])) {
		throw new \Exception("Can't create database structure");
	}
	$console->writeLine("OK!", ConsoleColorInterface::GREEN);
	
	$console->write('Populating database... ');
	if (!loadSqlFile($db, 'sql/init/data.sql', $dbConfig['table_prefix'])) {
		throw new \Exception("Can't populate database");
	}
	$console->writeLine("OK!", ConsoleColorInterface::GREEN);
} catch (\Exception $e) {
	$console->writeLine("KO!", ConsoleColorInterface::RED);
	exitWithError($e->getMessage(), 1);
}

// Demande url d'accès
$console->writeLine("\nAccess configuration:", ConsoleColorInterface::CYAN);
$accessUrl = "http://localhost/";
$validAccessUrlInput = false;
while (!$validAccessUrlInput) {
	$userAccessUrl = trim(
			ConsoleInput::prompt(
					$console->colorize("URL that will be used to access the website [http://localhost/]: ", ConsoleColorInterface::LIGHT_WHITE), true));
	if (!$userAccessUrl || $userAccessUrl === '') {
		$validAccessUrlInput = true;
	} else {
		$uri = new HttpUri($userAccessUrl);
		if ($uri->isValid()) {
			$accessUrl = $userAccessUrl;
			$validAccessUrlInput = true;
		} else {
			$console->writeLine("Invalid URL.", ConsoleColorInterface::RED);
		}
	}
}

// Demande et création data dir
$console->writeLine("\nStorage configuration:", ConsoleColorInterface::CYAN);
$console->writeLine("The system needs to store files (like pictures) in a specific storage path, with enought free space.");
$console->writeLine("You can use the data subdirectory as default, but this is not recommended.");
$console->writeLine('Note: this storage directory MUST be accessible and writable by the websever. / (root) path is not allowed', 
		ConsoleColorInterface::YELLOW);
$storagePath = trim(ConsoleInput::prompt($console->colorize("Storage path [data/miranda]: ", ConsoleColorInterface::LIGHT_WHITE), true));
$storagePath = rtrim($storagePath, '/');
if (!$storagePath || $storagePath === '') {
	$storagePath = 'data/miranda';
}
$console->writeLine("Creating storage directory $storagePath... ");
try {
	@mkdir($storagePath, 0775, true);
} catch (\Exception $e) {
}
if (is_dir($storagePath)) {
	$console->writeLine("OK!", ConsoleColorInterface::GREEN);
} else {
	$console->writeLine("KO!", ConsoleColorInterface::RED);
	$console->writeLine("Please create the directory $storagePath by yourself.", ConsoleColorInterface::RED);
}
try {
	@mkdir($storagePath . "/tmp", 0775, true);
} catch (\Exception $e) {
}

// Génération conf
$console->writeLine("\nConfiguration file creation:", ConsoleColorInterface::CYAN);
$config = [
	'application' => [
		'app' => [
			'base_url' => $accessUrl
		]
	],
	'db' => [
		'hostname' => $dbConfig['hostname'],
		'port' => $dbConfig['port'],
		'database' => $dbConfig['database'],
		'username' => $dbConfig['username'],
		'password' => $dbConfig['password'],
		'table_prefix' => $dbConfig['table_prefix']
	],
	'data_storage' => [
		'root_path' => $storagePath,
		'temp_path' => $storagePath . '/tmp'
	]
];

$configFileName = "config/autoload/miranda.local.php";
$phpConfigString = "<?php\nreturn " . var_export($config, true) . ";";
$console->writeLine("I can now generate the configuration file $configFileName with the following content:");
$console->writeLine($phpConfigString, ConsoleColorInterface::GRAY);
$console->writeLine("But this will overwrite previously existing configuration file with the same name (if any).");

if (ConsoleConfirm::prompt($console->colorize("Do you want me to create the configuration file?", ConsoleColorInterface::LIGHT_WHITE) . " [y/n] ")) {
	$console->writeLine("Writing configuration file $configFileName... ");
	if (file_put_contents($configFileName, $phpConfigString) !== false) {
		$console->writeLine("OK!", ConsoleColorInterface::GREEN);
	} else {
		$console->writeLine("KO!", ConsoleColorInterface::RED);
		$console->writeLine("Please, create the file with the following content:", ConsoleColorInterface::RED);
		$console->writeLine($phpConfigString, ConsoleColorInterface::GRAY);
	}
}
$console->writeLine("Please, ensure the webserver can read this file.", ConsoleColorInterface::LIGHT_YELLOW);
$console->writeLine(
		"Also, as this file contains secured information like database password, it's highly recommended that only webserver user can read this file, and nobody can write it.", 
		ConsoleColorInterface::LIGHT_YELLOW);

ConsoleInput::prompt("Press ENTER to continue...", true);

// Génération VHost
$console->writeLine("\nWebserver configuration:", ConsoleColorInterface::CYAN);
if (ConsoleConfirm::prompt(
		$console->colorize("Do you want me to generate a Apache VHost configuration file?", ConsoleColorInterface::LIGHT_WHITE) . " [y/n] ")) {
	$apacheConfigurationString = file_get_contents('config/apache/template.conf.dist');
	$apacheConfigurationFile = 'config/apache/miranda.conf';
	$uri = new HttpUri($accessUrl);
	$vars = [
		'ENV' => 'production',
		'SERVER_NAME' => $uri->getHost(),
		'SERVER_ADMIN' => 'root@localhost',
		'BASE_PATH' => rtrim(getcwd(), '/'),
		'DATA_PATH' => $storagePath
	];
	foreach ($vars as $var => $value) {
		$apacheConfigurationString = str_replace('{{' . $var . '}}', $value, $apacheConfigurationString);
	}
	$console->writeLine("Writing Apache VHost configuration file $apacheConfigurationFile... ");
	if (file_put_contents($apacheConfigurationFile, $apacheConfigurationString) !== false) {
		$console->writeLine("OK!", ConsoleColorInterface::GREEN);
	} else {
		$console->writeLine("KO!", ConsoleColorInterface::RED);
		$console->writeLine("Please, create the file with the following content:", ConsoleColorInterface::RED);
		$console->writeLine($apacheConfigurationString, ConsoleColorInterface::GRAY);
	}
}

// Création utilisateur admin principal
$console->writeLine("\nAdministrator user:", ConsoleColorInterface::CYAN);
$console->writeLine(
		"I'll now create a new user with administrator privileges. For this, I need at least a VALID email adress (as user will have to validate its account with a validation email).");

$emailValidator = new \Zend\Validator\EmailAddress();

do {
	do {
		$userEmail = trim(ConsoleInput::prompt($console->colorize("Valid user email: ", ConsoleColorInterface::LIGHT_WHITE), false));
		$isValid = $emailValidator->isValid($userEmail);
		if (!$isValid) {
			foreach ($emailValidator->getMessages() as $message) {
				$console->writeLine($message, ConsoleColorInterface::RED);
			}
		}
	} while (!$isValid);
	
	$userEmailConfirmation = trim(
			ConsoleInput::prompt($console->colorize("Valid user email (confirmation): ", ConsoleColorInterface::LIGHT_WHITE), false));
	
	$areSame = ($userEmailConfirmation == $userEmail);
	if (!$areSame) {
		$console->writeLine("Email addresses must be identical.", ConsoleColorInterface::RED);
	}
} while (!$areSame);
$userFirstname = trim(ConsoleInput::prompt($console->colorize("Firstname (optional)? ", ConsoleColorInterface::LIGHT_WHITE), true));
$userLastname = trim(ConsoleInput::prompt($console->colorize("Lastname (optional)? ", ConsoleColorInterface::LIGHT_WHITE), true));

if (!$userFirstname || (trim($userFirstname) == '')) {
	$userFirstname = "admin";
}
if (!$userLastname || (trim($userLastname) == '')) {
	$userLastname = "admin";
}

$console->writeLine("Creating administrator user... ");
passthru(
		"bin/miranda.php add user " . escapeshellarg($userEmail) . " --firstname " . escapeshellarg($userFirstname) . " --lastname " .
				 escapeshellarg($userLastname) . " --role Administrateur", $returnCode);
if ($returnCode) {
	$console->writeLine("KO!", ConsoleColorInterface::RED);
	$console->writeLine("Please, create the user by yourself running : bin/miranda.php add user --role Administrateur", 
			ConsoleColorInterface::RED);
}

$console->writeLine();
$console->writeLine("Installation is finished !", ConsoleColorInterface::GREEN);
