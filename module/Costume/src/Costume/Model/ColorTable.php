<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Application\Model\DataCache\DataCacheAwareInterface;
use Application\Model\DataCache\DataCacherTrait;

class ColorTable implements DataCacheAwareInterface
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

	protected function getMaxOrd()
	{
		$sql = $this->tableGateway->getSql();
		$adapter = $this->tableGateway->getAdapter();
		
		$select = $sql->select()->columns(array(
			'max_ord' => new Expression('MAX(ord)')
		));
		
		$results = $adapter->query($sql->getSqlStringForSqlObject($select), $adapter::QUERY_MODE_EXECUTE);
		if (count($results)) {
			$row = $results->current();
			if (isset($row->max_ord)) {
				return $row->max_ord;
			}
		}
		
		return null;
	}

	public function reorderColors(array $newOrder)
	{
		if (count($newOrder)) {
			$ord = 1;
			foreach ($newOrder as $id) {
				$id = (int)$id;
				if ($id) {
					$this->tableGateway->update(array(
						'ord' => $ord
					), array(
						'id' => $id
					));
					$ord++;
				}
			}
		}
	}

	/**
	 * Retoune toutes les couleurs existantes
	 *
	 * @return Color[] Liste des couleurs (sous forme d'un iterable)
	 */
	public function fetchAll()
	{
		if ($this->dataCacheIsComplete()) {
			return $this->dataCacheGetAll();
		}
		
		$data = $this->tableGateway->select(function ($select)
		{
			$select->order(array(
				'ord',
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

	public function getColor($id, $exceptionIfNone = true)
	{
		$id = (int)$id;
		if ($this->dataCacheIs($id)) {
			return $this->dataCacheGet($id);
		}
		
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$color = $rowset->current();
		if (!$color) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find color $id");
			} else {
				return false;
			}
		}
		
		return $color;
	}

	public function getColors(array $ids, $exceptionIfNone = true)
	{
		$colors = [];
		
		if (!count($ids)) {
			return false;
		}
		
		$getIds = array();
		foreach ($ids as $id) {
			$id = (int)$id;
			if ($this->dataCacheIs($id)) {
				$colors[$id] = $this->dataCacheGet($id);
				continue;
			} else {
				$getIds[] = $id;
			}
		}
		
		if (count($getIds)) {
			$rowset = $this->tableGateway->select(array(
				'id' => $getIds
			));
			foreach ($rowset as $color) {
				$colors[$color->getId()] = $color;
			}
		}
		
		return $colors;
	}

	public function getColorByName($name, $caseInsensitive = false, $exceptionIfNone = true)
	{
		if ($caseInsensitive) {
			$rowset = $this->tableGateway->select(function ($select) use($name)
			{
				$select->where->like('name', $name);
			});
		} else {
			$rowset = $this->tableGateway->select(array(
				'name' => $name
			));
		}
		$color = $rowset->current();
		if (!$color) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find color with name $name");
			} else {
				return false;
			}
		}
		
		return $color;
	}

	public function saveColor(Color $color)
	{
		$data = $color->getArrayCopy();
		
		$nextOrd = false;
		// Si pas d'ordre, on prend le prochain dans la base
		if (!array_key_exists('ord', $data) || empty($data['ord'])) {
			$max_ord = $this->getMaxOrd();
			if ($max_ord !== null) {
				$data['ord'] = $max_ord + 1;
			} else {
				$data['ord'] = 0;
			}
			$nextOrd = true;
		}
		
		$id = (int)$color->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
			$color->setId($id);
		} else {
			if ($this->getColor($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("Color id $id does not exist");
			}
		}
		
		if ($nextOrd) {
			$color->setOrd($data['ord']);
		}
	}

	public function deleteColor($id)
	{
		$this->tableGateway->delete(array(
			'id' => $id
		));
	}

	public function populateCache()
	{
		$colors = $this->fetchAll();
	}
}