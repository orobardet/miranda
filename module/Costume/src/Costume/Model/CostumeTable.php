<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Paginator\Paginator;
use Application\Model\DataCache\AbstractDataCachePopulator;

class CostumeTable extends AbstractDataCachePopulator
{
	
	/*
	 * @var TableGateway
	 */
	protected $tableGateway;

	/**
	 *
	 * @var \Costume\Model\CostumePictureTable
	 */
	protected $costumePictureTable;

	/**
	 *
	 * @var \Costume\Model\ColorTable
	 */
	protected $colorTable;

	/**
	 *
	 * @var \Costume\Model\TagTable
	 */
	protected $tagTable;
	
	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
		$this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype()->setCostumeTable($this);
	}

	/**
	 * Retoune tous les costumes existants
	 *
	 * @return Costume[] Liste des costumes (sous forme d'un iterable)
	 */
	public function fetchAll($usePaginator = false)
	{
		$this->populateCaches();
		
		if ($usePaginator) {
			$dbTableGatewayAdapter = new DbTableGateway($this->tableGateway);
			$rowset = new Paginator($dbTableGatewayAdapter);
		} else {
			$rowset = $this->tableGateway->select();
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
		
		return $costume;
	}

	public function saveCostume(Costume $costume)
	{
		// On enregistre les couleurs qui seraient nouvelles
		if ($this->colorTable) {
			$primaryColor = $costume->getPrimaryColor();
			if ($primaryColor && !$primaryColor->getId()) {
				$this->colorTable->saveColor($primaryColor);
				$costume->setPrimaryColor($primaryColor);
			}  
			$secondaryColor = $costume->getSecondaryColor();
			if ($secondaryColor && !$secondaryColor->getId()) {
				$this->colorTable->saveColor($secondaryColor);
				$costume->setSecondaryColor($secondaryColor);
			}  
		}
		
		$data = $costume->getArrayCopy();
		
		// Mise à jour de la date de modification
		$data['modification_ts'] = time();
		
		// Pas de date de création, on la défini à la date courane
		if (!$costume->getCreationDate()) {
			$data['creation_ts'] = time();
		}
		
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
		
		// Sauvegarde des images
		if ($this->costumePictureTable) {
			$this->costumePictureTable->saveCostumePictures($costume);
		}
		
		// Sauvegarde des tags
		if ($this->tagTable) {
			$this->tagTable->saveCostumeTags($costume);
		}
	}

	public function removeColor($colorId)
	{
		$this->tableGateway->update(array(
			'primary_color_id' => null
		), array(
			'primary_color_id' => $colorId
		));
		
		$this->tableGateway->update(array(
			'secondary_color_id' => null
		), array(
			'secondary_color_id' => $colorId
		));
	}

	public function deleteCostume($id)
	{
		$this->tableGateway->delete(array(
			'id' => $id
		));
	}

	public function populateCostumeData(Costume $costume)
	{
		if ($this->costumePictureTable) {
			$costume->setPictures($this->costumePictureTable->getCostumePictures($costume->getId()));
		}
		
		if ($this->colorTable) {
			$primaryColorId = $costume->getPrimaryColorId();
			if ($primaryColorId) {
				$primaryColor = $this->colorTable->getColor($primaryColorId, false);
				if ($primaryColor) {
					$costume->setPrimaryColor($primaryColor);
				} else {
					$costume->setPrimaryColorId(null);
				}
			}
			$secondaryColorId = $costume->getSecondaryColorId();
			if ($secondaryColorId) {
				$secondaryColor = $this->colorTable->getColor($secondaryColorId);
				if ($secondaryColor) {
					$costume->setSecondaryColor($secondaryColor);
				} else {
					$costume->setSecondaryColorId(null);
				}
			}
		}
		
		if ($this->tagTable) {
			$costume->setTags($this->tagTable->getCostumeTags($costume->getId()));
		}
	}

	/**
	 *
	 * @param \Costume\Model\CostumePictureTable $costumePictureTable
	 */
	public function setCostumePictureTable(CostumePictureTable $costumePictureTable)
	{
		$this->costumePictureTable = $costumePictureTable;
	}

	/**
	 *
	 * @param \Costume\Model\ColorTable $colorTable
	 */
	public function setColorTable(ColorTable $colorTable)
	{
		$this->colorTable = $colorTable;
		$this->addCachedCollection($colorTable);
	}
	
	/**
	 *
	 * @param \Costume\Model\TagTable $tagTable
	 */
	public function setTagTable(TagTable $tagTable)
	{
		$this->tagTable = $tagTable;
		$this->addCachedCollection($tagTable);
	}
}