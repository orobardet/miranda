<?php
namespace Costume\Form;

use Zend\Form\Form;
use Costume\Model\CostumeTable;
use Costume\Model\Costume as CostumeModel;

class Costume extends Form
{

	public function __construct(CostumeTable $costumeTable, $name = null, $translator = null)
	{
		parent::__construct($name);
		
		$costumeTable->populateCaches();
		
		$this->setAttribute('method', 'post');
		
		$this->add(array(
			'name' => 'id',
			'type' => 'Hidden',
			'attributes' => array(
				'id' => 'input-id'
			)
		));
		
		$this->add(
				array(
					'name' => 'code',
					'type' => 'Text',
					'options' => array(
						'label' => 'Code: '
					),
					'attributes' => array(
						'id' => 'input-code',
						'maxlength' => 20,
						'title' => 'Code',
						'placeholder' => 'e.g.: AB.CD.1'
					)
				));
		
		$this->add(
				array(
					'name' => 'label',
					'type' => 'Text',
					'options' => array(
						'label' => 'Name: '
					),
					'attributes' => array(
						'id' => 'input-label',
						'maxlength' => 255,
						'title' => 'Name',
						'placeholder' => 'Short description of the costume'
					)
				));
		
		$this->add(
				array(
					'name' => 'descr',
					'type' => 'Textarea',
					'options' => array(
						'label' => 'Description: '
					),
					'attributes' => array(
						'id' => 'input-description',
						'maxlength' => 65535,
						'title' => 'Description',
						'placeholder' => 'Complete and detailed description of the costume.'
					)
				));
		
		$this->add(
				array(
					'name' => 'size',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Size:',
						'value_options' => CostumeModel::getDefaultSizes(),
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-size',
						'title' => 'Size',
						'placeholder' => 'S, XL, or 38,42 or any text'
					)
				));
		
		$this->add(
				array(
					'name' => 'gender',
					'type' => 'Radio',
					'required' => true,
					'options' => array(
						'label' => 'Gender:',
						'value_options' => array_merge(array(
							'None'
						), CostumeModel::getGenders())
					),
					'attributes' => array(
						'id' => 'input-gender',
						'title' => 'Gender'
					)
				));
		
		$this->add(
				array(
					'name' => 'type_id',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Type:',
						'value_options' => $costumeTable->getTypes(),
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-type',
						'title' => 'Type',
						'placeholder' => 'Existing or new'
					)
				));
		
		$this->add(
				array(
					'name' => 'parts_selector',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Parts:',
						'value_options' => $costumeTable->getTypes(),
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-parts-selector',
						'title' => 'Parts',
						'placeholder' => "Existing or new, add it with '+'"
					)
				));
		
		$this->add(
				array(
					'name' => 'primary_material_id',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Primary material:',
						'value_options' => $costumeTable->getMaterials(),
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-primary-material-id',
						'title' => 'Primary material',
						'placeholder' => 'Existing or new'
					)
				));
		
		$this->add(
				array(
					'name' => 'secondary_material_id',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Secondary material:',
						'value_options' => $costumeTable->getMaterials(),
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-secondary-material-id',
						'title' => 'Secondary material',
						'placeholder' => 'Existing or new'
					)
				));
		
		$colors = $costumeTable->getColors();
		$preparedColors = array();
		if (count($colors)) {
			/* @var $color \Costume\Model\Color */
			foreach ($colors as $color) {
				$preparedColors[$color->getId()] = array('label' => $color->getName(), 'value' => $color->getId(), 'color' => $color->getColorCode());
			}
		}
		array_unshift($preparedColors, array('label' => '', 'value' => ''));
		
		$this->add(
				array(
					'name' => 'primary_color_id',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Primary color:',
						'value_options' => $preparedColors,
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-primary-color-id',
						'title' => 'Primary color',
						'placeholder' => 'No color'
					)
				));
		
		$this->add(
				array(
					'name' => 'secondary_color_id',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Secondary color:',
						'value_options' => $preparedColors,
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-secondary-color-id',
						'title' => 'Secondary color',
						'placeholder' => 'No color'
					)
				));
		
		$this->add(
				array(
					'name' => 'quantity',
					'type' => 'Number',
					'required' => true,
					'options' => array(
						'label' => 'Quantity:'
					),
					'attributes' => array(
						'id' => 'input-quantity',
						'min' => 1,
						'max' => 255,
						'step' => 1,
						'title' => 'Quantity'
					)
				));
		
		$this->add(
				array(
					'name' => 'state',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'State:',
						'value_options' => $costumeTable->getStates(),
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-state',
						'title' => 'State',
						'placeholder' => 'Existing or new'
					)
				));
		
		$this->add(
				array(
					'name' => 'origin',
					'type' => 'Select',
					'required' => false,
					'options' => array(
						'label' => 'Origin:',
						'value_options' => CostumeModel::getOrigins(),
					),
					'attributes' => array(
						'id' => 'input-origin',
						'title' => 'Origin'
					)
				));
		
		$this->add(
				array(
					'name' => 'origin_details',
					'type' => 'Text',
					'options' => array(
						'label' => 'Origin:'
					),
					'attributes' => array(
						'id' => 'input-origin-details',
						'maxlength' => 255,
						'title' => 'Origin details',
						'placeholder' => 'Origin details'
					)
				));
		
		$this->add(
				array(
					'name' => 'submit',
					'type' => 'Submit',
					'attributes' => array(
						'value' => 'Go',
						'id' => 'submitbutton'
					)
				));
	}
}
