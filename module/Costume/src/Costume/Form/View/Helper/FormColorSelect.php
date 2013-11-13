<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Costume\Form\View\Helper;

use Zend\Stdlib\ArrayUtils;
use Zend\Form\View\Helper\FormSelect;

class FormColorSelect extends FormSelect
{

	/**
	 * Render an array of options
	 *
	 * Individual options should be of the form:
	 *
	 * <code>
	 * array(
	 * 'value' => 'value',
	 * 'label' => 'label',
	 * 'disabled' => $booleanFlag,
	 * 'selected' => $booleanFlag,
	 * )
	 * </code>
	 *
	 * @param array $options
	 * @param array $selectedOptions Option values that should be marked as selected
	 * @return string
	 */
	public function renderOptions(array $options, array $selectedOptions = array())
	{
		$template = '<option %s>%s</option>';
		$optionStrings = array();
		$escapeHtml = $this->getEscapeHtmlHelper();
		$escapeHtmlAttr = $this->getEscapeHtmlAttrHelper();
		
		foreach ($options as $key => $optionSpec) {
			$value = '';
			$label = '';
			$selected = false;
			$disabled = false;
			$color = null;
			
			if (is_scalar($optionSpec)) {
				$optionSpec = array(
					'label' => $optionSpec,
					'value' => $key
				);
			}
			
			if (isset($optionSpec['options']) && is_array($optionSpec['options'])) {
				$optionStrings[] = $this->renderOptgroup($optionSpec, $selectedOptions);
				continue;
			}
			
			if (isset($optionSpec['value'])) {
				$value = $optionSpec['value'];
			}
			if (isset($optionSpec['label'])) {
				$label = $optionSpec['label'];
			}
			if (isset($optionSpec['selected'])) {
				$selected = $optionSpec['selected'];
			}
			if (isset($optionSpec['disabled'])) {
				$disabled = $optionSpec['disabled'];
			}
			if (isset($optionSpec['color'])) {
				$color = $optionSpec['color'];
			}
			
			if (ArrayUtils::inArray($value, $selectedOptions)) {
				$selected = true;
			}
			
			if (null !== ($translator = $this->getTranslator())) {
				$label = $translator->translate($label, $this->getTranslatorTextDomain());
			}
			
			$attributes = compact('value', 'selected', 'disabled');
			
			if ($color) {
				$optionSpec['attributes']['data-content'] = sprintf('<div class="costume-color-preview no-margin" style="background-color:#%s;"></div> %s', $escapeHtmlAttr($color), $escapeHtml($label));
			}
			
			if (isset($optionSpec['attributes']) && is_array($optionSpec['attributes'])) {
				$attributes = array_merge($attributes, $optionSpec['attributes']);
			}
			
			$this->validTagAttributes = $this->validOptionAttributes;
			$optionStrings[] = sprintf($template, $this->createAttributesString($attributes), $escapeHtml($label));
		}
		
		return implode("\n", $optionStrings);
	}
}
