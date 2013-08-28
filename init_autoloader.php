<?php
$zf2Path = false;

// ZFZ peut-être dans le répertoire par défaut local, ou dans un path fourni dans la variable d'environnement ZF2_PATH
if (is_dir('vendor/ZF2/library')) {
	$zf2Path = 'vendor/ZF2/library';
} elseif (getenv('ZF2_PATH')) {
	$zf2Path = getenv('ZF2_PATH');
}

if ($zf2Path) {
	// On tente d'abord de charger le ZF2 local ou défini par la variable d'environnement
	include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
} else {
	// Sinon, on tente de charger depuis l'include path PHP
	include 'Zend/Loader/AutoloaderFactory.php';
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
	throw new RuntimeException("Unable to load ZF2 (not found in default path 'vendor/ZF2/library'). You can define a 'ZF2_PATH' env variable.");
} else {
	Zend\Loader\AutoloaderFactory::factory(array(
		'Zend\Loader\StandardAutoloader' => array(
			'autoregister_zf' => true
		)
	));
}