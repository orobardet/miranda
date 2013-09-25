<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Paginator\Paginator;

class CostumeTable extends Costume
{

	/*
	 * @var TableGateway
	 */
	protected $tableGateway;
	/*
	 * @var CostumePictureTable
	 */
	protected $costumePictureTable;
	
	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	/**
	 * Retoune tous les costumes existants
	 *
	 * @return Costume[] Liste des costumes (sous forme d'un iterable)
	 */
	public function fetchAll($usePaginator = false)
	{
		if ($usePaginator) {
			$dbTableGatewayAdapter = new DbTableGateway($this->tableGateway);
			$rowset = new Paginator($dbTableGatewayAdapter);
		} else {
			$rowset = $this->tableGateway->select();
		}

		if (count($rowset)) {
 			foreach ($rowset as $costume) {
 				$this->populateCostumeData($costume);
 			}
		}
		
		return $rowset;
	}

	public function getCostume($id, $exceptionIfNone = true)
	{
		$id = (int)$id;
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$costume = $rowset->current();
		if (!$costume) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find costume $id");
			} else {
				return false;
			}
		}
		
		$this->populateCostumeData($costume);
		
		return $costume;
	}

	public function getCostumeByCode($code, $exceptionIfNone = true)
	{
		$rowset = $this->tableGateway->select(array(
			'code' => $code
		));
		$costume = $rowset->current();
		if (!$costume) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find costume with code '$code'");
			} else {
				return false;
			}
		}
		
		$this->populateCostumeData($costume);
		
		return $costume;
	}
	
	public function saveCostume(Costume $costume)
	{
		$data = $costume->getArrayCopy();
		
		$id = (int)$costume->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
			$costume->setId($id);
		} else {
			if ($this->getCostume($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("Costume id $id does not exist");
			}
		}
	}

	public function deleteCostume($id)
	{
		$this->tableGateway->delete(array(
			'id' => $id
		));
	}

	public function populateCostumeData($costume)
	{
		if ($this->costumePictureTable) {
			$costume->setPictures($this->costumePictureTable->getCostumePictures($costume->getId()));
		}
		
	}
	
	/**
	 * @param field_type $costumePictureTable
	 */
	public function setCostumePictureTable(CostumePictureTable $costumePictureTable)
	{
		$this->costumePictureTable = $costumePictureTable;
	}
}