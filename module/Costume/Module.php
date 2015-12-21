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
use Costume\Model\TagTable;
use Costume\Model\Tag;
use Costume\Model\MaterialTable;
use Costume\Model\Material;
use Costume\Model\TypeTable;
use Costume\Model\Type;

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
					$costumeTable = new CostumeTable($sm->get('Costume\TableGateway\Costumes'), $sm->get('Costume\TableGateway\Types'), 
							$sm->get('Costume\TableGateway\CostumeType'), $sm->get('Costume\TableGateway\CostumeTag'));
					$costumeTable->setCostumePictureTable($sm->get('Costume\Model\CostumePictureTable'));
					$costumeTable->setColorTable($sm->get('Costume\Model\ColorTable'));
					$costumeTable->setMaterialTable($sm->get('Costume\Model\MaterialTable'));
					$costumeTable->setTagTable($sm->get('Costume\Model\TagTable'));
					$costumeTable->setTypeTable($sm->get('Costume\Model\TypeTable'));
					return $costumeTable;
				},
				'Costume\Model\SearchCostumeTable' => function ($sm)
				{
					$costumeTableGateway = $sm->get('Costume\TableGateway\Costumes');
					$costumeTable = new CostumeTable($costumeTableGateway, $sm->get('Costume\TableGateway\Types'), 
							$sm->get('Costume\TableGateway\CostumeType'), $sm->get('Costume\TableGateway\CostumeTag'));
					$costumePrototype = $costumeTableGateway->getResultSetPrototype()->getArrayObjectPrototype();
					$costumePrototype->disableFeatures(['populatePictures', 'populateParts', 'populateTags']);
					$costumeTable->setCostumePictureTable($sm->get('Costume\Model\CostumePictureTable'));
					$costumeTable->setColorTable($sm->get('Costume\Model\ColorTable'));
					$costumeTable->setMaterialTable($sm->get('Costume\Model\MaterialTable'));
					$costumeTable->setTagTable($sm->get('Costume\Model\TagTable'));
					$costumeTable->setTypeTable($sm->get('Costume\Model\TypeTable'));
					return $costumeTable;
				},
				'Costume\Model\LightCostumeTable' => function ($sm)
				{
					$costumeTable = new CostumeTable($sm->get('Costume\TableGateway\Costumes'));
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
						$storagePath = realpath(
								rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $config->get('costume->pictures->store_path', ''));
						$picturePrototype->setStorageRoot($storagePath);
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
				'Costume\Model\MaterialTable' => function ($sm)
				{
					return new MaterialTable($sm->get('Costume\TableGateway\Material'));
				},
				'Costume\TableGateway\Material' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Material());
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costume_materials', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'Costume\Model\TagTable' => function ($sm)
				{
					return new TagTable($sm->get('Costume\TableGateway\Tag'), $sm->get('Costume\TableGateway\CostumeTag'));
				},
				'Costume\TableGateway\Tag' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Tag());
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costume_tags', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'Costume\TableGateway\CostumeTag' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costumes_tags', $dbAdapter, 
							new Feature\RowGatewayFeature(array(
								'costume_id',
								'tag_id'
							)));
				},
				'Costume\Model\TypeTable' => function ($sm)
				{
					return new TypeTable($sm->get('Costume\TableGateway\Types'), $sm->get('Costume\TableGateway\CostumeType'));
				},
				'Costume\TableGateway\Types' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Type());
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costume_types', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'Costume\TableGateway\CostumeType' => function ($sm)
				{
					$dbAdapter = $sm->get('costume_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'costumes_types', $dbAdapter, 
							new Feature\RowGatewayFeature(array(
								'costume_id',
								'type_id'
							)));
				},
				'Costume\Form\Search' => function ($sm)
				{
					$form = new Form\Search($sm->get('Costume\Model\CostumeTable'), null, $sm->get('translator'));
					// $form->setInputFilter(new Form\CostumeFilter($sm->get('costume_zend_db_adapter'), $sm->get('Miranda\Service\Config')));
					return $form;
				},
				'Costume\Form\Costume' => function ($sm)
				{
					$form = new Form\Costume($sm->get('Costume\Model\CostumeTable'), null, $sm->get('translator'));
					$form->setInputFilter(new Form\CostumeFilter($sm->get('costume_zend_db_adapter'), $sm->get('Miranda\Service\Config')));
					return $form;
				},
				'Costume\Form\Picture' => function ($sm)
				{
					$form = new Form\Picture();
					$form->setInputFilter(new Form\PictureFilter($sm->get('Miranda\Service\Config')));
					return $form;
				},
				'Costume\Hydrator\CostumeForm' => function ($sm)
				{
					$hydrator = new Model\Costume\FormHydrator($sm->get('Costume\Model\CostumeTable'), $sm->get('Costume\Model\TypeTable'), 
							$sm->get('Costume\Model\MaterialTable'));
					return $hydrator;
				}
			)
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'invokables' => array(
				'formColorSelect' => 'Costume\Form\View\Helper\FormColorSelect',
				'costumeGender' => 'Costume\View\Helper\CostumeGender'
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
			'import costumes <csv_file> [--picture-dir=<picture_dir>] [--tags=<tags>] [--log-file=<log_file>] [--error-file=<error_file>]' => 'Import costume CSV file, with picture (from prepared pictures directory)',
			array(
				'--picture-dir=<picture_dir>',
				'Directory containing prepared pictures for import.'
			),
			array(
				'--tags=<tags>',
				'Comma-separated list of tags to add to each imported costumes (do not forget to enclose between " ou \' if contains spaces).'
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
