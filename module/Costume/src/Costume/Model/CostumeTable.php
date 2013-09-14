<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;

class CostumeTable extends Costume
{

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	/**
	 * Retoune tous les costumes existants
	 *
	 * @return Costume[] Liste des costumes (sous forme d'un iterable)
	 */
	public function fetchAll()
	{
		return $this->tableGateway->select();
	}

	public function getCostume($id, $exceptionIfNone = true)
	{
		$id = (int)$id;
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$row = $rowset->current();
		if (!$row) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find costume $id");
			} else {
				return false;
			}
		}
		
		return $row;
	}

	public function getCostumeByCode($code, $exceptionIfNone = true)
	{
		$rowset = $this->tableGateway->select(array(
			'code' => $code
		));
		$row = $rowset->current();
		if (!$row) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find costume with code '$code'");
			} else {
				return false;
			}
		}
		
		return $row;
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
}