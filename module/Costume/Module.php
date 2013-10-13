<?php
namespace Costume;

use Costume\Model\Color;
use Costume\Model\ColorTable;
use Costume\Model\Costume;
use Costume\Model\CostumePictureTable;
use Costume\Model\CostumeTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

class Module implements AutoloaderProviderInterface, ConsoleUsageProviderInterface
{

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php'
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
				)
			)
		);
	}

	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'Costume\Model\CostumeTable' => function ($sm)
				{
					$costumeTable = new CostumeTable($sm->get('Costume\TableGateway\Costumes')); 
					$costumeTable->setCostumePictureTable($sm->get('Costume\Model\CostumePictureTable'));
					$costumeTable->setColorTable($sm->get('Costume\Model\ColorTable'));
					return $costumeTable;
				},
				'Costume\TableGateway\Costumes' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Costume());
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costumes', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'Costume\Model\CostumePictureTable' => function ($sm)
				{
					$config = $sm->get('Miranda\Service\Config');
					$picturesTableGateway = $sm->get('Miranda\TableGateway\Pictures');
					$picturePrototype = $picturesTableGateway->getResultSetPrototype()->getArrayObjectPrototype();
					$picturePrototype->setUrlRoot($config->get('costume->pictures->url_path', ''));
					$rootPath = $config->get('data_storage->root_path', '');
					if (!empty($rootPath)) {
						$picturePrototype->setStorageRoot(
								rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $config->get('costume->pictures->store_path', ''));
					} else {
						$picturePrototype->setStorageRoot($config->get('costume->pictures->store_path', ''));
					}
					return new CostumePictureTable($picturesTableGateway, $sm->get('Costume\TableGateway\CostumePicture'));
				},
				'Costume\TableGateway\CostumePicture' => function ($sm)
				{
					$dbAdapter = $sm->get('app_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costumes_pictures', $dbAdapter, 
							new Feature\RowGatewayFeature('costume_id'));
				},
				'Costume\Model\ColorTable' => function ($sm)
				{
					return new ColorTable($sm->get('Costume\TableGateway\Color'));
				},
				'Costume\TableGateway\Color' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Color());
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costume_colors', $dbAdapter, null, 
							$resultSetPrototype);
				},
			)
		);
	}

	public function getConsoleUsage(ConsoleAdapterInterface $console)
	{
		return array(
			'Costume management',
			'prepare costume pictures <input_dir> <output_dir>' => 'Scan, index, resize for pictures, creating an output directory ready for costume import',
			array(
				'<input_dir>',
				'Input directory of picture',
				'May contains JPEG (.jpg ou .jpeg, case insensitive) files'
			),
			array(
				'<output_dir>',
				'Output directory for prepared pictures',
				'Will be cleared at begining of the picture preparation. After preparation, contains all pictures files'
			),
			'import costumes <csv_file> [--picture-dir=<picture_dir>] [--log-file=<log_file>] [--error-file=<error_file>]' => 'Import costume CSV file, with picture (from prepared pictures directory)',
			array(
				'--picture-dir=<picture_dir>',
				'Directory containing prepared pictures for import.'
			),
			array(
				'--log-file=<log_file>',
				'Details of import will be logged in this file (overwritten).'
			),
			array(
				'--error-file=<error_file>',
				'Errors, warning, missing data or picture for import will be logged in this file (overwritten).'
			)
		);
	}
}
