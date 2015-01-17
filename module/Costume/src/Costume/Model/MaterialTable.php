<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\DataCache\DataCacheAwareInterface;
use Application\Model\DataCache\DataCacherTrait;

class MaterialTable implements DataCacheAwareInterface
{
	use DataCacherTrait;
	
	/*
	 * @var TableGateway
	 */
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	/**
	 * Retourne toutes les matières existantes
	 *
	 * @return Material[] Liste des matières (sous forme d'un iterable)
	 */
	public function fetchAll()
	{
		if ($this->dataCacheIsComplete()) {
			return $this->dataCacheGetAll();
		}
		
		$data = $this->tableGateway->select(function ($select)
		{
			$select->order(array(
				'name'
			));
		});
		
		if (count($data)) {
			foreach ($data as $item) {
				$this->dataCacheAdd($item->getId(), $item);
			}
			$this->dataCacheComplete();
		}
		return $data;
	}

	public function getMaterial($id, $exceptionIfNone = true)
	{
		$id = (int)$id;
		if ($this->dataCacheIs($id)) {
			return $this->dataCacheGet($id);
		}
		
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$material = $rowset->current();
		if (!$material) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find material $id");
			} else {
				return false;
			}
		}
		
		return $material;
	}

	public function getMaterials(array $ids, $exceptionIfNone = true)
	{
		$materials = [];
		
		if (!count($ids)) {
			return false;
		}
		
		$getIds = array();
		foreach ($ids as $id) {
			$id = (int)$id;
			if ($this->dataCacheIs($id)) {
				$materials[$id] = $this->dataCacheGet($id);
				continue;
			} else {
				$getIds[] = $id;
			}
		}
		
		if (count($getIds)) {
			$rowset = $this->tableGateway->select(array(
				'id' => $getIds
			));
			foreach ($rowset as $material) {
				$materials[$material->getId()] = $material;
			}
		}
		
		return $materials;
	}
	
	public function getMaterialByName($name, $caseInsensitive = false, $exceptionIfNone = true)
	{
		if ($caseInsensitive) {
			$rowset = $this->tableGateway->select(
					function ($select) use($name)
					{
						$select->where->like('name', $name);
					});
		} else {
			$rowset = $this->tableGateway->select(array(
				'name' => $name
			));
		}
		$material = $rowset->current();
		if (!$material) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find material with name $name");
			} else {
				return false;
			}
		}
		
		return $material;
	}

	public function saveMaterial(Material $material)
	{
		$data = $material->getArrayCopy();
		
		$id = (int)$material->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
			$material->setId($id);
		} else {
			if ($this->getMaterial($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("Material id $id does not exist");
			}
		}
	}

	public function deleteMaterial($id)
	{
		$this->tableGateway->delete(array(
			'id' => $id
		));
	}

	public function populateCache()
	{
		$materials = $this->fetchAll();
	}
}