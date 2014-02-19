<?php
namespace Costume\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Application\Model\DataCache\AbstractDataCachePopulator;
use Zend\Db\Sql\Expression;
use ArrayObject;

class CostumeTable extends AbstractDataCachePopulator
{
	
	/*
	 * @var TableGateway
	 */
	protected $tableGateway;

	/*
	 * @var TableGateway
	 */
	protected $typeTableGateway;

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
	 * @var \Costume\Model\MaterialTable
	 */
	protected $materialTable;

	/**
	 *
	 * @var \Costume\Model\TagTable
	 */
	protected $tagTable;

	/**
	 *
	 * @var \Costume\Model\TypeTable
	 */
	protected $typeTable;

	public function __construct(TableGateway $tableGateway, TableGateway $typeTableGateway)
	{
		$this->tableGateway = $tableGateway;
		$this->typeTableGateway = $typeTableGateway;
		$this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype()->setCostumeTable($this);		
	}

	/**
	 * Retoune tous les costumes existants
	 *
	 * @return Costume[] Liste des costumes (sous forme d'un iterable)
	 */
	public function fetchAll($usePaginator = false, $order = null)
	{
		$this->populateCaches();
		
		$select = $this->tableGateway->getSql()->select();
		if (!empty($order)) {
			if ($order) {
				preg_match('/^\s*([^\s]+).*?(asc|desc)?\s*$/iu', $order, $matches);
				if (count($matches) > 1) {
					$field = $matches[1];
					$direction = 'ASC';
						
					if ($field == 'type') {
						$typeTableName = $this->typeTableGateway->getTable();
						$select->join($typeTableName, $typeTableName . '.id = ' . $this->tableGateway->getTable() . '.type_id', array(), 'left');
						$field = $typeTableName.'.name';
					}
					
					if (count($matches) > 2) {
						$direction = $matches[2];
					}
					$select->order("$field $direction");
				}
			}
		}
		
//		echo $select->getSqlString($this->tableGateway->adapter->platform); die;
		
		if ($usePaginator) {
			$dbTableGatewayAdapter = new DbSelect($select, $this->tableGateway->adapter, $this->tableGateway->getResultSetPrototype());
			$rowset = new Paginator($dbTableGatewayAdapter);
		} else {
			$rowset = $this->tableGateway->select($select);
		}
		
		return $rowset;
	}

	/**
	 * Charge un costume depuis la BDD, par ID
	 *
	 * @param integer $id
	 * @param boolean $exceptionIfNone
	 *
	 * @throws \Exception
	 * @return Costume
	 */
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

	/**
	 * Charge un costume depuis la BDD, par code de costume
	 *
	 * @param string $code
	 * @param boolean $exceptionIfNone
	 *
	 * @throws \Exception
	 * @return Costume
	 */
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
		
		// On enregistre les matières qui seraient nouvelles
		if ($this->materialTable) {
			$primaryMaterial = $costume->getPrimaryMaterial();
			if ($primaryMaterial && !$primaryMaterial->getId()) {
				$this->materialTable->saveMaterial($primaryMaterial);
				$costume->setPrimaryMaterial($primaryMaterial);
			}
			$secondaryMaterial = $costume->getSecondaryMaterial();
			if ($secondaryMaterial && !$secondaryMaterial->getId()) {
				$this->materialTable->saveMaterial($secondaryMaterial);
				$costume->setSecondaryMaterial($secondaryMaterial);
			}
		}
		
		// On enregistre les type qui seraient nouveaux
		if ($this->typeTable) {
			$type = $costume->getType();
			if ($type && !$type->getId()) {
				$this->typeTable->saveType($type);
				$costume->setType($type);
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
		
		// Sauvegarde des parts
		if ($this->typeTable) {
			$this->typeTable->saveCostumeParts($costume);
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

	public function removeMaterial($materialId)
	{
		$this->tableGateway->update(array(
			'primary_material_id' => null
		), array(
			'primary_material_id' => $materialId
		));
		
		$this->tableGateway->update(array(
			'secondary_material_id' => null
		), array(
			'secondary_material_id' => $materialId
		));
	}

	public function removeTag($tagId)
	{
		if ($this->tagTable) {
			$this->tagTable->removeTagFromCostumes($tagId);
		}
	}

	public function removeType($typeId)
	{
		$this->tableGateway->update(array(
			'type_id' => null
		), array(
			'type_id' => $typeId
		));
		if ($this->typeTable) {
			$this->typeTable->removeTypeFromCostumes($typeId);
		}
	}

	public function deleteCostume($id)
	{
		$costume = $this->getCostume($id, false);
		
		if ($costume) {
			// On supprime les images du costume
			if ($this->costumePictureTable) {
				$this->costumePictureTable->deleteCostumePictures($costume);
			}
			
			// On supprime l'association des tags à un costume (mais pas les tags eux-même)
			if ($this->tagTable) {
				$this->tagTable->removeCostumeTags($costume->getId());
			}
			
			// On supprime l'association des types compsant un costume (mais pas les pièces eux-même)
			if ($this->typeTable) {
				$this->typeTable->removeCostumeTypes($costume->getId());
			}
			
			$this->tableGateway->delete(array(
				'id' => $id
			));
		}
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
		
		if ($this->materialTable) {
			$primaryMaterialId = $costume->getPrimaryMaterialId();
			if ($primaryMaterialId) {
				$primaryMaterial = $this->materialTable->getMaterial($primaryMaterialId, false);
				if ($primaryMaterial) {
					$costume->setPrimaryMaterial($primaryMaterial);
				} else {
					$costume->setPrimaryMaterialId(null);
				}
			}
			$secondaryMaterialId = $costume->getSecondaryMaterialId();
			if ($secondaryMaterialId) {
				$secondaryMaterial = $this->materialTable->getMaterial($secondaryMaterialId);
				if ($secondaryMaterial) {
					$costume->setSecondaryMaterial($secondaryMaterial);
				} else {
					$costume->setSecondaryMaterialId(null);
				}
			}
		}
		
		if ($this->tagTable) {
			$costume->setTags($this->tagTable->getCostumeTags($costume->getId()));
		}
		
		if ($this->typeTable) {
			$typeId = $costume->getTypeId();
			if ($typeId) {
				$type = $this->typeTable->getType($typeId, false);
				if ($type) {
					$costume->setType($type);
				} else {
					$costume->setTypeId(null);
				}
			}
			
			$costume->setParts($this->typeTable->getCostumeParts($costume->getId()));
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
	 * @param \Costume\Model\MaterialTable $materialTable
	 */
	public function setMaterialTable(MaterialTable $materialTable)
	{
		$this->materialTable = $materialTable;
		$this->addCachedCollection($materialTable);
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

	/**
	 *
	 * @param \Costume\Model\TypeTable $typeTable
	 */
	public function setTypeTable(TypeTable $typeTable)
	{
		$this->typeTable = $typeTable;
		$this->addCachedCollection($typeTable);
	}

	/**
	 * Retourne la liste des types, sous forme d'un tableau.
	 *
	 * clé = ID type, valeur = nom type
	 *
	 * @return array
	 */
	public function getTypes($labelAsKey = false)
	{
		$types = array();
		
		if ($this->typeTable) {
			$allTypes = $this->typeTable->fetchAll();
			if (count($allTypes)) {
				foreach ($allTypes as $type) {
					if ($labelAsKey) {
						$types[$type->getName()] = $type->getName();
					} else {
						$types[$type->getId()] = $type->getName();
					}
				}
			}
		}
		
		return $types;
	}

	/**
	 * Retourne la liste des matières, sous forme d'un tableau.
	 *
	 * clé = ID matière, valeur = nom matière
	 *
	 * @return array
	 */
	public function getMaterials($labelAsKey = false)
	{
		$materials = array();
		
		if ($this->materialTable) {
			$allMaterials = $this->materialTable->fetchAll();
			if (count($allMaterials)) {
				foreach ($allMaterials as $material) {
					if ($labelAsKey) {
						$materials[$material->getName()] = $material->getName();
					} else {
						$materials[$material->getId()] = $material->getName();
					}
				}
			}
		}
		
		return $materials;
	}

	/**
	 * Retourne la liste des tags, sous forme d'un tableau.
	 *
	 * clé = ID type, valeur = nom type
	 *
	 * @return array
	 */
	public function getTags($labelAsKey = false)
	{
		$tags = array();
		
		if ($this->tagTable) {
			$allTags = $this->tagTable->fetchAll();
			if (count($allTags)) {
				foreach ($allTags as $tag) {
					if ($labelAsKey) {
						$tags[$tag->getName()] = $tag->getName();
					} else {
						$tags[$tag->getId()] = $tag->getName();
					}
				}
			}
		}
		
		return $tags;
	}

	/**
	 * Retourne la liste des couleurs, sous forme d'un tableau.
	 *
	 * clé = ID couleur, valeur = nom couleur
	 *
	 * @return array
	 */
	public function getColors()
	{
		$colors = array();
		
		if ($this->colorTable) {
			$colors = $this->colorTable->fetchAll();
		}
		
		return $colors;
	}

	/**
	 * Retourne la liste des etats déjà saisi dans la BDD
	 *
	 * @return string[]
	 */
	public function getStates()
	{
		/* @var $sqlSelect \Zend\Db\Sql\Select */
		$sqlSelect = $this->tableGateway->getSql()->select();
		$sqlSelect->columns(array(
			new Expression('DISTINCT(state) AS state')
		));
		$sqlSelect->where(array(
			'state IS NOT NULL',
			'state != ?' => ''
		));
		$sqlSelect->order('state');
		
		/* @var $resultSet \Zend\Db\ResultSet\ResultSet */
		$resultSet = $this->tableGateway->selectWith($sqlSelect);
		$resultSet->setArrayObjectPrototype(new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS));
		
		$states = array();
		foreach ($resultSet as $state) {
			$states[] = $state->state;
		}
		
		return $states;
	}
}