<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\DataCache\AbstractDataCacher;
use Application\Model\DataCache\DataCacheAwareInterface;

class TypeTable extends AbstractDataCacher implements DataCacheAwareInterface
{
	
	/*
	 * @var TableGateway
	 */
	protected $tableGateway;
	/*
	 * @var TableGateway
	 */
	protected $costumeTypeGateway;

	public function __construct(TableGateway $tableGateway, TableGateway $costumeTypeGateway)
	{
		$this->tableGateway = $tableGateway;
		$this->costumeTypeGateway = $costumeTypeGateway;
	}

	/**
	 * Retourne tous les types existant
	 *
	 * @return \Costume\Model\Type[] Liste des types (sous forme d'un iterable)
	 */
	public function fetchAll()
	{
		if ($this->dataCacheCount()) {
			return $this->dataCacheGetAll();
		} else {
			return $this->tableGateway->select(function ($select)
			{
				$select->order(array(
					'name'
				));
			});
		}
	}

	public function getType($id, $exceptionIfNone = true)
	{
		if ($this->dataCacheIs($id)) {
			return $this->dataCacheGet($id);
		}
		
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$type = $rowset->current();
		if (!$type) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find type $id");
			} else {
				return false;
			}
		}
		
		return $type;
	}

	public function getTypeByName($name, $exceptionIfNone = true)
	{
		$rowset = $this->tableGateway->select(function ($select) use($name)
		{
			$select->where->like('name', $name);
		});
		$type = $rowset->current();
		if (!$type) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find type with name $name");
			} else {
				return false;
			}
		}
		
		return $type;
	}

	/**
	 *
	 * @param integer $id ID du costume dont on veut les types
	 * @throws \Exception
	 * @return \Costume\Model\Type[]
	 */
	public function getCostumeParts($id)
	{
		$assocTable = $this->costumeTypeGateway->getTable();
		/* @var $sqlSelect \Zend\Db\Sql\Select */
		$sqlSelect = $this->tableGateway->getSql()->select();
		$sqlSelect->join($assocTable, $assocTable . '.type_id = ' . $this->tableGateway->getTable() . '.id', array(), 'left');
		$sqlSelect->where(array(
			'costume_id = ?' => $id
		));
		$sqlSelect->order(array(
			'name'
		));
		$resultSet = $this->tableGateway->selectWith($sqlSelect);
		
		return $resultSet;
	}

	public function saveCostumeParts($costume)
	{
		$costumeId = $costume->getId();
		$currentTypes = $this->getCostumeParts($costumeId);
		$types = $costume->getParts();
		
		$currentTypeIds = array();
		$typeIds = array();
		
		if (count($currentTypes)) {
			foreach ($currentTypes as $type) {
				$currentTypeIds[] = $type->getId();
			}
		}
		if (count($types)) {
			foreach ($types as $type) {
				if ($type->getName()) {
					$typeId = $type->getId();
					// Si le type n'a pas d'ID et qu'il n'y a pas déjà ce type, on l'enregistre d'abord
					if (!$typeId) {
						$existingType = $this->getTypeByName($type->getName(), false);
						if ($existingType) {
							$type = $existingType;
						} else {
							$this->saveType($type);
						}
						$typeId = $type->getId();
					}
					$typeIds[] = $typeId;
				}
			}
		}
		
		// On retire tous les ID qui ne sont plus dans la liste actuelle
		$removedTypes = array_diff($currentTypeIds, $typeIds);
		if (count($removedTypes)) {
			foreach ($removedTypes as $id) {
				$this->costumeTypeGateway->delete(array(
					'type_id' => $id
				));
				$this->deleteType($id);
			}
		}
		
		// On ajoute tous les ID qui sont nouveaux dans la liste actuelle
		$newTypes = array_diff($typeIds, $currentTypeIds);
		if (count($newTypes)) {
			foreach ($newTypes as $id) {
				$this->costumeTypeGateway->insert(array(
					'costume_id' => $costumeId,
					'type_id' => $id
				));
			}
		}
	}

	public function saveType(Type $type)
	{
		$data = $type->getArrayCopy();
		
		$id = (int)$type->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
			$type->setId($id);
		} else {
			if ($this->getType($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("Type id $id does not exist");
			}
		}
	}

	public function removeTypeFromCostumes($id)
	{
		// On supprime le type de tous les costumes
		$this->costumeTypeGateway->delete(array(
			'type_id' => $id
		));
	}

	public function removeCostumeTypes($costume_id)
	{
		// On supprime tous les type du costume
		$this->costumeTypeGateway->delete(array(
			'costume_id' => $costume_id
		));
	}

	public function deleteType($id)
	{
		// On ne supprime que si le type n'est plus utilisé
		$results = $this->costumeTypeGateway->select(array(
			'type_id' => $id
		));
		if (!count($results)) {
			$this->tableGateway->delete(array(
				'id' => $id
			));
		}
	}

	public function populateCache()
	{
		$types = $this->fetchAll();
		if (count($types)) {
			foreach ($types as $type) {
				$this->dataCacheAdd($type->getId(), $type);
			}
		}
	}
}