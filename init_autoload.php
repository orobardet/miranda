<?php
if (file_exists('vendor/autoload.php')) {
    include 'vendor/autoload.php';
}
if (class_exists('Zend\Loader\AutoloaderFactory')) {
    return;
}

$zf2Path = false;
if (getenv('ZF2_PATH')) {            // Support for ZF2_PATH environment variable
    $zf2Path = getenv('ZF2_PATH');
} elseif (get_cfg_var('zf2_path')) { // Support for zf2_path directive value
    $zf2Path = get_cfg_var('zf2_path');
}
if ($zf2Path) {
    if (isset($loader)) {
        $loader->add('Zend', $zf2Path);
        $loader->add('ZendXml', $zf2Path);
    } else {
        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';

        $zf2AutoLoaderConfig = [];
        if (file_exists(__DIR__ . '/vendor/composer/autoload_classmap.php')) {
            $zf2AutoLoaderConfig += [
                'Zend\Loader\ClassMapAutoloader' => [
                    'Composer' => __DIR__ . '/vendor/composer/autoload_classmap.php'
                ]
            ];
        }

        $zf2AutoLoaderConfig += [
            'Zend\Loader\StandardAutoloader' => [
                'autoregister_zf' => true
            ]
        ];

        Zend\Loader\AutoloaderFactory::factory($zf2AutoLoaderConfig);
    }
}
if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException('Unable to load ZF2. Run composer or define a ZF2_PATH environment variable.');
}
