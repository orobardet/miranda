<?php
require_once __DIR__ . '/vendor/zendframework/zendframework/library/Zend/Loader/AutoloaderFactory.php';
require_once __DIR__ . '/vendor/zendframework/zendframework/library/Zend/Loader/ClassMapAutoloader.php';

Zend\Loader\AutoloaderFactory::factory(
		array(
			'Zend\Loader\ClassMapAutoloader' => array(
				'Composer' => __DIR__ . '/vendor/composer/autoload_classmap.php'
			),
			'Zend\Loader\StandardAutoloader' => array(
				'autoregister_zf' => true
			)
		));
