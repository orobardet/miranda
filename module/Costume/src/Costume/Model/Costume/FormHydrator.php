<?php
namespace Costume\Model\Costume;

use Costume\Model\Costume;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Costume\Model\CostumeTable;
use Costume\Model\Tag;
use Costume\Model\Type;
use Costume\Model\TypeTable;
use Costume\Model\MaterialTable;
use Costume\Model\Material;

class FormHydrator implements HydratorInterface
{

	/**
	 *
	 * @var \Costume\Model\CostumeTable
	 */
	protected $costumeTable;
	/**
	 *
	 * @var \Costume\Model\TypeTable
	 */
	protected $typeTable;
	/**
	 *
	 * @var \Costume\Model\MaterialTable
	 */
	protected $materialTable;
	
	public function __construct(CostumeTable $costumeTable, TypeTable $typeTable, MaterialTable $materialTable)
	{
		$this->costumeTable = $costumeTable;
		$this->typeTable = $typeTable;
		$this->materialTable = $materialTable;
	}

	/**
	 *
	 * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
	 *
	 * @param \Costume\Model\Costume $costume
	 */
	public function extract($costume)
	{
		$data = array();
		if ($costume instanceof Costume) {
			// Le plus gros du boulot est fait par le getArrayCopy
			$data = $costume->getArrayCopy();
			
			// Matière principale
			$material = $costume->getPrimaryMaterial();
			if ($material) {
				$data['primary_material'] = $material->getName();
				$data['primary_material_id'] = $material->getId();
			}
			
			// Matière secondaire
			$material = $costume->getSecondaryMaterial();
			if ($material) {
				$data['secondary_material'] = $material->getName();
				$data['secondary_material_id'] = $material->getId();
			}
			
			// Type
			$type = $costume->getType();
			if ($type) {
				$data['type'] = $type->getName();
				$data['type_id'] = $type->getId();
			}
			
			// Composition
			$parts = $costume->getParts();
			if (count($parts)) {
				$data['parts'] = array();
				foreach ($parts as $part) {
					$data['parts'][] = $part->getName();
				}
			}
			
			// Tags
			$tags = $costume->getTags();
			if (count($tags)) {
				$data['tags'] = array();
				foreach ($tags as $tag) {
					$data['tags'][] = $tag->getName();
				}
			}
		}
		
		return $data;
	}

	/**
	 *
	 * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
	 *
	 * @param array $data
	 * @param \Costume\Model\Costume $costume
	 *
	 * @return \Costume\Model\Costume
	 */
	public function hydrate(array $data, $costume)
	{
		if ($costume instanceof Costume) {
			$costume->setCostumeTable($this->costumeTable);
			
			// Le plus gros du boulot est fait par le exchangeArray de l'objet
			$costume->exchangeArray($data);
			
			// Detection du la matière principale existante ou création d'une nouvelle
			if (array_key_exists('primary_material', $data) && ($data['primary_material'] !== '')) {
				$materialName = $data['primary_material'];
				$material = $this->materialTable->getMaterialByName($materialName, true, false);
				if (!$material) {
					$material = new Material($materialName);
				}
				$costume->setPrimaryMaterial($material);
			}
			// Detection du la matière secondaire existante ou création d'une nouvelle
			if (array_key_exists('secondary_material', $data) && ($data['secondary_material'] !== '')) {
				$materialName = $data['secondary_material'];
				$material = $this->materialTable->getMaterialByName($materialName, true, false);
				if (!$material) {
					$material = new Material($materialName);
				}
				$costume->setSecondaryMaterial($material);
			}
			
			// Detection du type existant ou création d'un nouveau
			if (array_key_exists('type', $data) && ($data['type'] !== '')) {
				$typeName = $data['type'];
				$type = $this->typeTable->getTypeByName($typeName, false);
				if (!$type) {
					$type = new Type($typeName);
				}
				$costume->setType($type);
			}
			
			// Liste des pièces composant le costume
			// Sous forme de nouveaux types, la détection des existants est faite par la sauvegarde de costume 
			if (array_key_exists('parts', $data) && count($data['parts'])) {
				$parts = array();
				foreach ($data['parts'] as $part) {
					$parts[] = new Type($part);
				}
				$costume->setParts($parts);
			}
			
			// Liste de tags
			// Sous forme de nouveaux tags, la détection des existants est faite par la sauvegarde de costume 
			if (array_key_exists('tags', $data) && count($data['tags'])) {
				$tags = array();
				foreach ($data['tags'] as $tag) {
					$tags[] = new Tag($tag);
				}
				$costume->setTags($tags);
			}
		}
				
		return $costume;
	}
}
