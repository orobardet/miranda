<?php
namespace Costume\Form;

use Zend\Form\Form;
use Costume\Model\CostumeTable;
use Costume\Model\Costume as CostumeModel;
use Zend\InputFilter\InputFilterProviderInterface;

class Search extends Form implements InputFilterProviderInterface
{

	/**
	 *
	 * @var array
	 */
	protected $inputFilterSpecification = null;

	public function getInputFilterSpecification()
	{
		if (!$this->inputFilterSpecification) {
			$this->inputFilterSpecification = array();
			$elements = $this->getElements();
			if (count($elements)) {
				foreach ($elements as $name => $input) {
					$this->inputFilterSpecification[$name] = array(
						'required' => false
					);
				}
			}
		}
		
		return $this->inputFilterSpecification;
	}

	public function __construct(CostumeTable $costumeTable, $name = null, $translator = null)
	{
		parent::__construct($name);
		
		$costumeTable->populateCaches();
		
		$this->setAttribute('method', 'get');
		$this->setPreferFormInputFilter(true);
		
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
					'type' => 'Text',
					'options' => array(
						'label' => 'Description: '
					),
					'attributes' => array(
						'id' => 'input-description',
						'maxlength' => 65535,
						'title' => 'Description',
						'placeholder' => 'Description'
					)
				));
		
		$sizes = $costumeTable->getSizes();
		array_unshift($sizes, array(
			'label' => '<none>',
			'value' => '##none##'
		));
		array_unshift($sizes, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'size',
					'type' => 'Select',
					'options' => array(
						'label' => 'Size:',
						'value_options' => $sizes,
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-size',
						'title' => 'Size'
					)
				));
		
		$genders = CostumeModel::getGenders();
		foreach ($genders as $key => &$value) {
			$val = $value;
			$value = array(
				'label' => $val,
				'value' => $val
			);
		}
		array_unshift($genders, array(
			'label' => '<none>',
			'value' => '##none##'
		));
		array_unshift($genders, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'gender',
					'type' => 'Select',
					'options' => array(
						'label' => 'Gender:',
						'value_options' => $genders
					),
					'attributes' => array(
						'id' => 'input-gender',
						'title' => 'Gender'
					)
				));
		
		$types = $costumeTable->getTypes();
		foreach ($types as $key => &$value) {
			$val = $value;
			$value = array(
				'label' => $val,
				'value' => $key
			);
		}
		$parts = $types;
		array_unshift($types, array(
			'label' => '<none>',
			'value' => '##none##'
		));
		array_unshift($types, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'type_id',
					'type' => 'Select',
					'options' => array(
						'label' => 'Type:',
						'value_options' => $types
					),
					'attributes' => array(
						'id' => 'input-type',
						'title' => 'Type'
					)
				));
		
		array_unshift($parts, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'parts_selector',
					'type' => 'Select',
					'options' => array(
						'label' => 'Parts:',
						'value_options' => $parts
					),
					'attributes' => array(
						'id' => 'input-parts-selector',
						'title' => 'Parts',
						'placeholder' => 'Parts'
					)
				));
		
		$this->add(array(
			'name' => 'parts',
			'type' => 'Text',
			'options' => array(
				'label' => ''
			)
		));
		
		$materials = $costumeTable->getMaterials();
		foreach ($materials as $key => &$value) {
			$val = $value;
			$value = array(
				'label' => $val,
				'value' => $key
			);
		}
		array_unshift($materials, array(
			'label' => '<none>',
			'value' => '##none##'
		));
		array_unshift($materials, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'primary_material_id',
					'type' => 'Select',
					'options' => array(
						'label' => 'Primary material:',
						'value_options' => $materials
					),
					'attributes' => array(
						'id' => 'input-primary-material-id',
						'title' => 'Primary material'
					)
				));
		
		$this->add(
				array(
					'name' => 'secondary_material_id',
					'type' => 'Select',
					'options' => array(
						'label' => 'Secondary material:',
						'value_options' => $materials
					),
					'attributes' => array(
						'id' => 'input-secondary-material-id',
						'title' => 'Secondary material'
					)
				));
		
		$colors = $costumeTable->getColors();
		$preparedColors = array();
		if (count($colors)) {
			/* @var $color \Costume\Model\Color */
			foreach ($colors as $color) {
				$preparedColors[$color->getId()] = array(
					'label' => $color->getName(),
					'value' => $color->getId(),
					'color' => $color->getColorCode()
				);
			}
		}
		array_unshift($preparedColors, array(
			'label' => '<none>',
			'value' => '##none##'
		));
		array_unshift($preparedColors, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'primary_color_id',
					'type' => 'Select',
					'options' => array(
						'label' => 'Primary color:',
						'value_options' => $preparedColors
					),
					'attributes' => array(
						'id' => 'input-primary-color-id',
						'title' => 'Primary color'
					)
				));
		
		$this->add(
				array(
					'name' => 'secondary_color_id',
					'type' => 'Select',
					'options' => array(
						'label' => 'Secondary color:',
						'value_options' => $preparedColors
					),
					'attributes' => array(
						'id' => 'input-secondary-color-id',
						'title' => 'Secondary color'
					)
				));
		
		$states = $costumeTable->getStates();
		array_unshift($states, array(
			'label' => '<none>',
			'value' => '##none##'
		));
		array_unshift($states, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'state',
					'type' => 'Select',
					'options' => array(
						'label' => 'State:',
						'value_options' => $states,
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-state',
						'title' => 'State'
					)
				));
		
		$origins = array_combine(CostumeModel::getOrigins(), CostumeModel::getOrigins());
		array_unshift($origins, array(
			'label' => '<none>',
			'value' => '##none##'
		));
		array_unshift($origins, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'origin',
					'type' => 'Select',
					'options' => array(
						'label' => 'Origin:',
						'value_options' => $origins
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
						'label' => 'Origin details:'
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
					'name' => 'history',
					'type' => 'Text',
					'options' => array(
						'label' => 'History:'
					),
					'attributes' => array(
						'id' => 'input-history',
						'maxlength' => 65535,
						'title' => 'History',
						'placeholder' => 'History'
					)
				));
		
		$tags = $costumeTable->getTags();
		foreach ($tags as $key => &$value) {
			$val = $value;
			$value = array(
				'label' => $val,
				'value' => $key
			);
		}
		array_unshift($tags, array(
			'label' => '',
			'value' => null
		));
		$this->add(
				array(
					'name' => 'tags_selector',
					'type' => 'Select',
					'options' => array(
						'label' => 'Tags:',
						'value_options' => $tags,
						'disable_inarray_validator' => true
					),
					'attributes' => array(
						'id' => 'input-tags-selector',
						'title' => 'Tags',
						'placeholder' => "Tags"
					)
				));
		
		$this->add(array(
			'name' => 'tags',
			'type' => 'Text',
			'options' => array(
				'label' => ''
			)
		));
	}

	public function getSearchData()
	{
		$data = $this->getData();
		
		// On retire les champs "hors recherche"
		$ignoredFields = array(
			'sort',
			'direction'
		);
		
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
