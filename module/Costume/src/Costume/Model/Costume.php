<?php
namespace Costume\Model;

use Application\Model\ObjectModelBase;
use Costume\Model\Color;
use Application\Model\Picture;

class Costume extends ObjectModelBase
{

	const GENDER_MIXED = 'Mixte';

	const GENDER_MAN = 'Homme';

	const GENDER_WOMAN = 'Femme';

	const GENDER_NONE = null;

	/**
	 *
	 * @var \Costume\Model\CostumeTable
	 */
	protected $costumeTable;

	/**
	 * ID en BDD du costume
	 *
	 * @var integer
	 */
	protected $id;

	/**
	 * Code (référence, côte)
	 *
	 * @var string
	 */
	protected $code;

	/**
	 * Libellé
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * Timestamp de la date de création du costume
	 *
	 * @var integer
	 */
	protected $creation_ts;

	/**
	 * Timestamp de la date de dernière modification du costume
	 *
	 * @var integer
	 */
	protected $modification_ts;

	/**
	 * Description
	 *
	 * @var string
	 */
	protected $descr;

	/**
	 * Genre (Homme/Femme/Mixte)
	 *
	 * @var string
	 */
	protected $gender;

	/**
	 * Taille du costume
	 *
	 * @var string
	 */
	protected $size;

	/**
	 * Etat du costume
	 *
	 * @var string
	 */
	protected $state;

	/**
	 * Quantité
	 *
	 * @var integer
	 */
	protected $quantity;

	/**
	 * Liste des images
	 *
	 * @var \Application\Model\Picture[]
	 */
	protected $pictures;

	/**
	 * ID de la couleur principale
	 *
	 * @var integer
	 */
	protected $primary_color_id;

	/**
	 * Couleur principale
	 *
	 * @var \Costume\Model\Color
	 */
	protected $primary_color;

	/**
	 * ID de la couleur secondaire
	 *
	 * @var integer
	 */
	protected $secondary_color_id;

	/**
	 * Couleur secondaire
	 *
	 * @var \Costume\Model\Color
	 */
	protected $secondary_color;

	/**
	 * ID de la matière principale
	 *
	 * @var integer
	 */
	protected $primary_material_id;

	/**
	 * Matière principale
	 *
	 * @var \Costume\Model\Material
	 */
	protected $primary_material;

	/**
	 * ID de la matière secondaire
	 *
	 * @var integer
	 */
	protected $secondary_material_id;

	/**
	 * Matière secondaire
	 *
	 * @var \Costume\Model\Material
	 */
	protected $secondary_material;

	/**
	 * Liste des tags
	 *
	 * @var \Costume\Model\Tag[] Tableau de Tag ou de chaine
	 */
	protected $tags;

	/**
	 * ID du type
	 *
	 * @var integer
	 */
	protected $type_id;

	/**
	 * Type du costume
	 *
	 * @var \Costume\Model\Type
	 */
	protected $type;

	/**
	 * Types d'éléments composant le costume
	 *
	 * @var \Costume\Model\Type[]
	 */
	protected $parts;

	/**
	 *
	 * @return integer $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return string $code
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 *
	 * @return string $label
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Retourne la date de création du compte, éventuellement formatée
	 *
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date()
	 * et selon le format du paramètre $format.
	 *
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé,
	 * retourne la chaine "N/A".
	 *
	 * __Exemples :__
	 *
	 * ~~~~~~~~
	 * $this->getCreationDate(); // Retourne le timestamp
	 * $this->getCreationDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string int
	 */
	public function getCreationDate($format = null)
	{
		return $this->getFormatedDate($this->creation_ts, $format);
	}

	/**
	 * Retourne la date de dernière modification du compte, éventuellement formatée
	 *
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date()
	 * et selon le format du paramètre $format.
	 *
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé,
	 * retourne la chaine "N/A".
	 *
	 * __Exemples :__
	 *
	 * ~~~~~~~~
	 * $this->getLastModificationDate(); // Retourne le timestamp
	 * $this->getLastModificationDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string int
	 */
	public function getLastModificationDate($format = null)
	{
		return $this->getFormatedDate($this->modification_ts, $format);
	}

	/**
	 *
	 * @return string $descr
	 */
	public function getDescr()
	{
		return $this->descr;
	}

	/**
	 *
	 * @return string $gender
	 */
	public function getGender()
	{
		return $this->gender;
	}

	/**
	 *
	 * @return string $size
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 *
	 * @return string $state
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 *
	 * @return string $quantity
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 *
	 * @return integer $primary_color_id
	 */
	public function getPrimaryColorId()
	{
		return $this->primary_color_id;
	}

	/**
	 *
	 * @return \Costume\Model\Color $primary_color
	 */
	public function getPrimaryColor()
	{
		return $this->primary_color;
	}

	/**
	 *
	 * @return integer $secondary_color_id
	 */
	public function getSecondaryColorId()
	{
		return $this->secondary_color_id;
	}

	/**
	 *
	 * @return \Costume\Model\Color $secondary_color
	 */
	public function getSecondaryColor()
	{
		return $this->secondary_color;
	}

	/**
	 *
	 * @return integer $primary_material_id
	 */
	public function getPrimaryMaterialId()
	{
		return $this->primary_material_id;
	}

	/**
	 *
	 * @return \Costume\Model\Material $primary_material
	 */
	public function getPrimaryMaterial()
	{
		return $this->primary_material;
	}

	/**
	 *
	 * @return integer $secondary_material_id
	 */
	public function getSecondaryMaterialId()
	{
		return $this->secondary_material_id;
	}

	/**
	 *
	 * @return \Costume\Model\Material $secondary_material
	 */
	public function getSecondaryMaterial()
	{
		return $this->secondary_material;
	}

	/**
	 *
	 * @return Picture[] $pictures
	 */
	public function getPictures()
	{
		return $this->pictures;
	}

	/**
	 *
	 * @return \Costume\Model\Tag[]:
	 */
	public function getTags()
	{
		if ($this->tags) {
			return $this->tags;
		} else {
			return array();
		}
	}

	/**
	 *
	 * @return integer $type_id
	 */
	public function getTypeId()
	{
		return $this->type_id;
	}

	/**
	 *
	 * @return \Costume\Model\Type $type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 *
	 * @return \Costume\Model\Type[]:
	 */
	public function getParts()
	{
		if ($this->parts) {
			return $this->parts;
		} else {
			return array();
		}
	}

	/**
	 *
	 * @param integer $id
	 */
	public function setId($id)
	{
		$this->id = intval($id);
	}

	/**
	 *
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 *
	 * @param string $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 *
	 * @param $descr
	 */
	public function setDescr($descr)
	{
		$this->descr = $descr;
	}

	/**
	 *
	 * @param string $gender
	 */
	public function setGender($gender)
	{
		$this->gender = $gender;
	}

	/**
	 *
	 * @param string $size
	 */
	public function setSize($size)
	{
		$this->size = $size;
	}

	/**
	 *
	 * @param string $state
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 *
	 * @param integer $quantity
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = intval($quantity);
	}

	/**
	 *
	 * @param integer $color
	 */
	public function setPrimaryColorId($colorId)
	{
		$this->primary_color_id = $colorId;
		if ($this->primary_color) {
			if ($this->primary_color->getId() !== $this->primary_color_id) {
				$this->primary_color = null;
			}
		}
	}

	/**
	 *
	 * @param \Costume\Model\Color $color
	 */
	public function setPrimaryColor(Color $color)
	{
		$this->primary_color = $color;
		if ($this->primary_color) {
			$this->primary_color_id = $color->getId();
		}
	}

	/**
	 *
	 * @param integer $color
	 */
	public function setSecondaryColorId($colorId)
	{
		$this->secondary_color_id = $colorId;
		if ($this->secondary_color) {
			if ($this->secondary_color->getId() !== $this->secondary_color_id) {
				$this->secondary_color = null;
			}
		}
	}

	/**
	 *
	 * @param \Costume\Model\Color $color
	 */
	public function setSecondaryColor(Color $color)
	{
		$this->secondary_color = $color;
		if ($this->secondary_color) {
			$this->secondary_color_id = $color->getId();
		}
	}

	/**
	 *
	 * @param integer $material
	 */
	public function setPrimaryMaterialId($materialId)
	{
		$this->primary_material_id = $materialId;
		if ($this->primary_material) {
			if ($this->primary_material->getId() !== $this->primary_material_id) {
				$this->primary_material = null;
			}
		}
	}

	/**
	 *
	 * @param \Costume\Model\Material $material
	 */
	public function setPrimaryMaterial(Material $material)
	{
		$this->primary_material = $material;
		if ($this->primary_material) {
			$this->primary_material_id = $material->getId();
		}
	}

	/**
	 *
	 * @param integer $material
	 */
	public function setSecondaryMaterialId($materialId)
	{
		$this->secondary_material_id = $materialId;
		if ($this->secondary_material) {
			if ($this->secondary_material->getId() !== $this->secondary_material_id) {
				$this->secondary_material = null;
			}
		}
	}

	/**
	 *
	 * @param \Costume\Model\Material $material
	 */
	public function setSecondaryMaterial(Material $material)
	{
		$this->secondary_material = $material;
		if ($this->secondary_material) {
			$this->secondary_material_id = $material->getId();
		}
	}

	/**
	 *
	 * @param integer $typeId
	 */
	public function setTypeId($typeId)
	{
		$this->type_id = $typeId;
		if ($this->type) {
			if ($this->type->getId() !== $this->type_id) {
				$this->type = null;
			}
		}
	}

	/**
	 *
	 * @param \Costume\Model\Type $type
	 */
	public function setType(Type $type)
	{
		$this->type = $type;
		if ($this->type) {
			$this->type_id = $type->getId();
		}
	}

	/**
	 *
	 * @param \Application\Model\Picture $pictures
	 */
	public function addPicture(Picture $picture)
	{
		if (!$this->pictures) {
			$this->pictures = array();
		}
		$this->pictures[] = $picture;
	}

	/**
	 *
	 * @param \Application\Model\Picture $pictures
	 */
	public function setPictures($pictures)
	{
		$this->pictures = $pictures;
	}

	/**
	 *
	 * @param \Costume\Model\Tag[] $tags Tableau de Tag ou de chaine
	 */
	public function setTags($tags)
	{
		$this->tags = array();
		if (count($tags)) {
			foreach ($tags as $tag) {
				$this->tags[] = $tag;
			}
		}
	}

	/**
	 *
	 * @param \Costume\Model\Tag[] $tag
	 */
	public function addTags($tags)
	{
		if (!$this->tags) {
			$this->tags = array();
		}
		
		if (count($tags)) {
			$addTags = array();
			foreach ($tags as $tag) {
				$addTags[] = $tag;
			}
			
			$this->tags = array_unique(array_merge($this->tags, $addTags));
		}
	}

	/**
	 *
	 * @param \Costume\Model\Tag $tag
	 */
	public function addTag($tag)
	{
		if (!$this->tags) {
			$this->tags = array();
		}
		
		$this->tags[] = $tag;
	}

	/**
	 *
	 * @param \Costume\Model\Type[] $parts Tableau de Type ou de chaine
	 */
	public function setParts($parts)
	{
		$this->parts = array();
		if (count($parts)) {
			foreach ($parts as $part) {
				$this->parts[] = $part;
			}
		}
	}

	/**
	 *
	 * @param \Costume\Model\Type[] $parts
	 */
	public function addTypes($parts)
	{
		if (!$this->parts) {
			$this->parts = array();
		}
		
		if (count($parts)) {
			$addParts = array();
			foreach ($parts as $part) {
				$addParts[] = $part;
			}
			
			$this->parts = array_unique(array_merge($this->parts, $addParts));
		}
	}

	/**
	 *
	 * @param \Costume\Model\Type $part
	 */
	public function addType($part)
	{
		if (!$this->parts) {
			$this->parts = array();
		}
		
		$this->parts[] = $part;
	}

	public function setCostumeTable($costumeTable)
	{
		$this->costumeTable = $costumeTable;
	}

	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->code = (array_key_exists('code', $data)) ? $data['code'] : null;
		$this->label = (array_key_exists('label', $data)) ? $data['label'] : null;
		$this->creation_ts = (array_key_exists('creation_ts', $data)) ? $data['creation_ts'] : $this->creation_ts;
		$this->modification_ts = (array_key_exists('modification_ts', $data)) ? $data['modification_ts'] : $this->modification_ts;
		$this->descr = (array_key_exists('descr', $data)) ? $data['descr'] : null;
		$this->gender = (array_key_exists('gender', $data)) ? $data['gender'] : null;
		$this->size = (array_key_exists('size', $data)) ? $data['size'] : null;
		$this->state = (array_key_exists('state', $data)) ? $data['state'] : $this->state;
		$this->quantity = (array_key_exists('quantity', $data)) ? $data['quantity'] : $this->quantity;
		$this->primary_color_id = (array_key_exists('primary_color_id', $data)) ? $data['primary_color_id'] : $this->primary_color_id;
		$this->secondary_color_id = (array_key_exists('secondary_color_id', $data)) ? $data['secondary_color_id'] : $this->secondary_color_id;
		$this->primary_material_id = (array_key_exists('primary_material_id', $data)) ? $data['primary_material_id'] : $this->primary_material_id;
		$this->secondary_material_id = (array_key_exists('secondary_material_id', $data)) ? $data['secondary_material_id'] : $this->secondary_material_id;
		$this->type_id = (array_key_exists('type_id', $data)) ? $data['type_id'] : $this->type_id;
		
		if ($this->costumeTable) {
			$this->costumeTable->populateCostumeData($this);
		}
	}

	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'code' => $this->code,
			'label' => $this->label,
			'descr' => $this->descr,
			'gender' => $this->gender,
			'size' => $this->size,
			'state' => $this->state,
			'quantity' => $this->quantity,
			'primary_color_id' => $this->primary_color_id,
			'secondary_color_id' => $this->secondary_color_id,
			'primary_material_id' => $this->primary_material_id,
			'secondary_material_id' => $this->secondary_material_id,
			'type_id' => $this->type_id
		);
	}
}