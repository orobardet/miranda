<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormNumber;

class FormUxSpinner extends FormNumber
{

	/**
	 * Render a form <input> element from the provided $element
	 *
	 * @param ElementInterface $element
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element)
	{
		$classes = $element->getAttribute('class');
		if ($classes) {
			$classes = explode(' ', $classes);
			if (!in_array('input-mini', $classes)) {
				$classes[] = 'input-mini';
			}
			if (!in_array('spinner-input', $classes)) {
				$classes[] = 'spinner-input';
			}
		} else {
			$classes = array('input-mini', 'spinner-input');
		}
		$input = parent::render($element->setAttribute('class', join(' ', $classes)));

		return sprintf(
				'<div class="spinner">
					%s
					<div class="spinner-buttons	btn-group btn-group-vertical">
						<button type="button" class="btn btn-mini spinner-up">
							<i class="icon-chevron-up"></i>
						</button>
						<button type="button" class="btn btn-mini spinner-down">
							<i class="icon-chevron-down"></i>
						</button>
					</div>
				</div>', $input);
	}
}
