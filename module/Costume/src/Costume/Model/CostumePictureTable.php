<?php
namespace Costume\Model;

use Application\Model\PictureTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class CostumePictureTable extends PictureTable
{

	protected $costumePictureGateway;

	public function __construct(TableGateway $tableGateway, TableGateway $costumePictureGateway)
	{
		$this->costumePictureGateway = $costumePictureGateway;
		parent::__construct($tableGateway);
	}

	public function fetchAll()
	{
		$result = $this->select(
				function (Select $select)
				{
					$select->join('users', 'blogs_settings.owner_id = users.user_id', array(
						'username'
					));
				});
		return $result;
	}

	/**
	 *
	 * @param integer $id ID du costume dont on veut les images
	 * @throws \Exception
	 * @return boolean Picture[]
	 */
	public function getCostumePictures($id)
	{
		$assocTable = $this->costumePictureGateway->getTable();
		/* @var $sqlSelect Select */
		$sqlSelect = $this->tableGateway->getSql()->select();
		$sqlSelect->join($assocTable, $assocTable . '.picture_id = ' . $this->tableGateway->getTable() . '.id', array(), 'left');
		$sqlSelect->where(array(
			'costume_id = ?' => $id
		));
		
		$resultSet = $this->tableGateway->selectWith($sqlSelect);
		
		return $resultSet;
	}
}
