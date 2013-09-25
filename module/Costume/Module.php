<?php
namespace Costume;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Costume\Model\CostumeTable;
use Zend\Db\ResultSet\ResultSet;
use Costume\Model\Costume;
use Zend\Db\TableGateway\TableGateway;
use Costume\Model\CostumePictureTable;
use Zend\Db\TableGateway\Feature;

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
					return $costumeTable;
				},
				'Costume\TableGateway\Costumes' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Costume());
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'costumes', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'Costume\Model\CostumePictureTable' => function ($sm)
				{
					$config = $sm->get('Miranda\Service\Config');
					$picturesTableGateway = $sm->get('Miranda\TableGateway\Pictures');
					$picturePrototype = $picturesTableGateway->getResultSetPrototype()->getArrayObjectPrototype();
					$picturePrototype->setUrlRoot($config->costume->pictures->get('url_path', ''));
					$rootPath = $config->data->get('root_path', '');
					if (!empty($rootPath)) {
						$picturePrototype->setStorageRoot(
								rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $config->costume->pictures->get('storage_path', ''));
					} else {
						$picturePrototype->setStorageRoot($config->costume->pictures->get('storage_path', ''));
					}
					return new CostumePictureTable($picturesTableGateway, $sm->get('Costume\TableGateway\CostumePicture'));
				},
				'Costume\TableGateway\CostumePicture' => function ($sm)
				{
					$dbAdapter = $sm->get('app_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'costumes_pictures', $dbAdapter, 
							new Feature\RowGatewayFeature('costume_id'));
				}
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
