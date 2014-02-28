<?php
namespace Costume\Form;

use Zend\Form\Form;
use Costume\Model\CostumeTable;

class Search extends Form
{

	public function __construct(CostumeTable $costumeTable, $name = null, $translator = null)
	{
		parent::__construct($name);
		
		$costumeTable->populateCaches();
		
		$this->setAttribute('method', 'get');
		
		$this->add(array(
			'name' => 'sort',
			'type' => 'Hidden'
		));
		
		$this->add(array(
			'name' => 'direction',
			'type' => 'Hidden'
		));
		
		$this->add(
				array(
					'name' => 'q',
					'type' => 'Text',
					'options' => array(
						'label' => 'Search'
					),
					'attributes' => array(
						'id' => 'input-q',
						'title' => 'Search',
						'placeholder' => 'Search',
						'autocomplete' => 'off'
					)
				));
		
// 		$this->add(
// 				array(
// 					'name' => 'code',
// 					'type' => 'Text',
// 					'options' => array(
// 						'label' => 'Code: '
// 					),
// 					'attributes' => array(
// 						'id' => 'input-code',
// 						'maxlength' => 20,
// 						'title' => 'Code',
// 						'placeholder' => 'e.g.: AB.CD.1'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'label',
// 					'type' => 'Text',
// 					'options' => array(
// 						'label' => 'Name: '
// 					),
// 					'attributes' => array(
// 						'id' => 'input-label',
// 						'maxlength' => 255,
// 						'title' => 'Name',
// 						'placeholder' => 'Short description of the costume'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'descr',
// 					'type' => 'Textarea',
// 					'options' => array(
// 						'label' => 'Description: '
// 					),
// 					'attributes' => array(
// 						'id' => 'input-description',
// 						'maxlength' => 65535,
// 						'title' => 'Description',
// 						'placeholder' => 'Complete and detailed description of the costume.'
// 					)
// 				));
		
// 		$defaultSizes = CostumeModel::getDefaultSizes();
// 		$this->add(
// 				array(
// 					'name' => 'size',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Size:',
// 						'value_options' => array_combine($defaultSizes, $defaultSizes),
// 						'disable_inarray_validator' => true
// 					),
// 					'attributes' => array(
// 						'id' => 'input-size',
// 						'title' => 'Size',
// 						'placeholder' => 'S, XL, or 38,42 or any text'
// 					)
// 				));
		
// 		$genders = CostumeModel::getGenders();
// 		$this->add(
// 				array(
// 					'name' => 'gender',
// 					'type' => 'Radio',
// 					'options' => array(
// 						'label' => 'Gender:',
// 						'value_options' => array_merge(array(
// 							'' => 'None',
// 						), array_combine($genders, $genders))
// 					),
// 					'attributes' => array(
// 						'id' => 'input-gender',
// 						'title' => 'Gender'
// 					)
// 				));
		
// 		$types = $costumeTable->getTypes(true);
// 		$this->add(
// 				array(
// 					'name' => 'type',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Type:',
// 						'value_options' => $types,
// 						'disable_inarray_validator' => true
// 					),
// 					'attributes' => array(
// 						'id' => 'input-type',
// 						'title' => 'Type',
// 						'placeholder' => 'Existing or new'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'parts_selector',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Parts:',
// 						'value_options' => $types,
// 						'disable_inarray_validator' => true
// 					),
// 					'attributes' => array(
// 						'id' => 'input-parts-selector',
// 						'title' => 'Parts',
// 						'placeholder' => "Existing or new, add it with '+'"
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'parts',
// 					'type' => 'Text',
// 					'options' => array(
// 						'label' => ''
// 					)
// 				));
		
// 		$materials = $costumeTable->getMaterials(true);
// 		$this->add(
// 				array(
// 					'name' => 'primary_material',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Primary material:',
// 						'value_options' => $materials,
// 						'disable_inarray_validator' => true
// 					),
// 					'attributes' => array(
// 						'id' => 'input-primary-material-id',
// 						'title' => 'Primary material',
// 						'placeholder' => 'Existing or new'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'secondary_material',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Secondary material:',
// 						'value_options' => $materials,
// 						'disable_inarray_validator' => true
// 					),
// 					'attributes' => array(
// 						'id' => 'input-secondary-material-id',
// 						'title' => 'Secondary material',
// 						'placeholder' => 'Existing or new'
// 					)
// 				));
		
// 		$colors = $costumeTable->getColors();
// 		$preparedColors = array();
// 		if (count($colors)) {
// 			/* @var $color \Costume\Model\Color */
// 			foreach ($colors as $color) {
// 				$preparedColors[$color->getId()] = array('label' => $color->getName(), 'value' => $color->getId(), 'color' => $color->getColorCode());
// 			}
// 		}
// 		array_unshift($preparedColors, array('label' => '', 'value' => ''));
		
// 		$this->add(
// 				array(
// 					'name' => 'primary_color_id',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Primary color:',
// 						'value_options' => $preparedColors
// 					),
// 					'attributes' => array(
// 						'id' => 'input-primary-color-id',
// 						'title' => 'Primary color',
// 						'placeholder' => 'No color'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'secondary_color_id',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Secondary color:',
// 						'value_options' => $preparedColors
// 					),
// 					'attributes' => array(
// 						'id' => 'input-secondary-color-id',
// 						'title' => 'Secondary color',
// 						'placeholder' => 'No color'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'quantity',
// 					'type' => 'Number',
// 					'options' => array(
// 						'label' => 'Quantity:'
// 					),
// 					'attributes' => array(
// 						'id' => 'input-quantity',
// 						'min' => 1,
// 						'max' => 255,
// 						'step' => 1,
// 						'title' => 'Quantity'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'state',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'State:',
// 						'value_options' => $costumeTable->getStates(),
// 						'disable_inarray_validator' => true
// 					),
// 					'attributes' => array(
// 						'id' => 'input-state',
// 						'title' => 'State',
// 						'placeholder' => 'Existing or new'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'origin',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Origin:',
// 						'value_options' => array_merge(array('' => ''), array_combine(CostumeModel::getOrigins(), CostumeModel::getOrigins())),
// 					),
// 					'attributes' => array(
// 						'id' => 'input-origin',
// 						'title' => 'Origin',
// 						'placeholder' => 'No origin'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'origin_details',
// 					'type' => 'Text',
// 					'options' => array(
// 						'label' => 'Origin:'
// 					),
// 					'attributes' => array(
// 						'id' => 'input-origin-details',
// 						'maxlength' => 255,
// 						'title' => 'Origin details',
// 						'placeholder' => 'Origin details'
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'history',
// 					'type' => 'Textarea',
// 					'options' => array(
// 						'label' => 'History:'
// 					),
// 					'attributes' => array(
// 						'id' => 'input-history',
// 						'maxlength' => 65535,
// 						'title' => 'History',
// 						'placeholder' => "Usage history of the costume. One per line, for example in form 'Show (character)."
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'tags_selector',
// 					'type' => 'Select',
// 					'options' => array(
// 						'label' => 'Tags:',
// 						'value_options' => $costumeTable->getTags(),
// 						'disable_inarray_validator' => true
// 					),
// 					'attributes' => array(
// 						'id' => 'input-tags-selector',
// 						'title' => 'Tags',
// 						'placeholder' => "Existing or new, add it with '+'"
// 					)
// 				));
		
// 		$this->add(
// 				array(
// 					'name' => 'tags',
// 					'type' => 'Text',
// 					'options' => array(
// 						'label' => ''
// 					)
// 				));
	}
	
	public function getSearchData()
	{
		$data = $this->getData();
		
		// On retire les champs "hors recherche"
		$ignoredFields = array('sort', 'direction');
		
		$searchData = array();
		foreach ($data as $key => $val) {
			if (!in_array($key, $ignoredFields)) {
				if (($val !== null) && ($val != '')) {
					$searchData[$key] = $val;
				}
			}
		}
	
		return $searchData;
	}	
}
