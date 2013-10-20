<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\DataCache\AbstractDataCacher;
use Application\Model\DataCache\DataCacheAwareInterface;

class TagTable extends AbstractDataCacher implements DataCacheAwareInterface
{
	
	/*
	 * @var TableGateway
	 */
	protected $tableGateway;
	/*
	 * @var TableGateway
	 */
	protected $costumeTagGateway;

	public function __construct(TableGateway $tableGateway, TableGateway $costumeTagGateway)
	{
		$this->tableGateway = $tableGateway;
		$this->costumeTagGateway = $costumeTagGateway;
	}

	/**
	 * Retourne tous les tags existant
	 *
	 * @return \Costume\Model\Tag[] Liste des tags (sous forme d'un iterable)
	 */
	public function fetchAll()
	{
		return $this->tableGateway->select(function ($select)
		{
			$select->order(array(
				'name'
			));
		});
	}

	public function getTag($id, $exceptionIfNone = true)
	{
		if ($this->dataCacheIs($id)) {
			return $this->dataCacheGet($id);
		}
		
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$tag = $rowset->current();
		if (!$tag) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find tag $id");
			} else {
				return false;
			}
		}
		
		return $tag;
	}

	public function getTagByName($name, $exceptionIfNone = true)
	{
		$rowset = $this->tableGateway->select(function ($select) use($name)
		{
			$select->where->like('name', $name);
		});
		$tag = $rowset->current();
		if (!$tag) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find tag with name $name");
			} else {
				return false;
			}
		}
		
		return $tag;
	}

	/**
	 *
	 * @param integer $id ID du costume dont on veut les tags
	 * @throws \Exception
	 * @return boolean tags[]
	 */
	public function getCostumeTags($id)
	{
		$assocTable = $this->costumeTagGateway->getTable();
		/* @var $sqlSelect \Zend\Db\Sql\Select */
		$sqlSelect = $this->tableGateway->getSql()->select();
		$sqlSelect->join($assocTable, $assocTable . '.tag_id = ' . $this->tableGateway->getTable() . '.id', array(), 'left');
		$sqlSelect->where(array(
			'costume_id = ?' => $id
		));
		$sqlSelect->order(array(
			'name'
		));
		$resultSet = $this->tableGateway->selectWith($sqlSelect);
		
		return $resultSet;
	}

	public function saveCostumeTags($costume)
	{
		$costumeId = $costume->getId();
		$currentTags = $this->getCostumeTags($costumeId);
		$tags = $costume->getTags();
		
		$currentTagIds = array();
		$tagIds = array();
		
		if (count($currentTags)) {
			foreach ($currentTags as $tag) {
				$currentTagIds[] = $tag->getId();
			}
		}
		if (count($tags)) {
			foreach ($tags as $tag) {
				if ($tag->getName()) {
					$tagId = $tag->getId();
					// Si l'image n'a pas d'ID et qu'il n'y a pas déjà ce tag, on l'enregistre d'abord
					if (!$tagId) {
						$existingTag = $this->getTagByName($tag->getName(), false);
						if ($existingTag) {
							$tag = $existingTag;
						} else {
							$this->saveTag($tag);
						}
						$tagId = $tag->getId();
					}
					$tagIds[] = $tagId;
				}
			}
		}
		
		// On retire tous les ID qui ne sont plus dans la liste actuelle
		$removedTags = array_diff($currentTagIds, $tagIds);
		if (count($removedTags)) {
			foreach ($removedTags as $id) {
				$this->costumeTagGateway->delete(array(
					'tag_id' => $id
				));
				$this->deleteTag($id);
			}
		}
		
		// On ajoute tous les ID qui sont nouveaux dans la liste actuelle
		$newTags = array_diff($tagIds, $currentTagIds);
		if (count($newTags)) {
			foreach ($newTags as $id) {
				$this->costumeTagGateway->insert(array(
					'costume_id' => $costumeId,
					'tag_id' => $id
				));
			}
		}
	}

	public function saveTag(Tag $tag)
	{
		$data = $tag->getArrayCopy();
		
		$id = (int)$tag->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
			$tag->setId($id);
		} else {
			if ($this->getTag($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("Tag id $id does not exist");
			}
		}
	}
	
	public function removeTagFromCostumes($id)
	{
		// On supprime le tag de tous les costumes
		$this->costumeTagGateway->delete(array(
			'tag_id' => $id
		));
	}
	
	public function deleteTag($id)
	{
		// On ne supprime que si le tag n'est plus utilisé
		$results = $this->costumeTagGateway->select(array(
			'tag_id' => $id
		));
		if (!count($results)) {
			$this->tableGateway->delete(array(
				'id' => $id
			));
		}
	}
	
	public function populateCache()
	{
		$tags = $this->fetchAll();
		if (count($tags)) {
			foreach ($tags as $tag) {
				$this->dataCacheAdd($tag->getId(), $tag);
			}
		}
	}
}