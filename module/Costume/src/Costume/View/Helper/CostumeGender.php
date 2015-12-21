<?php
namespace Costume\View\Helper;

use Costume\Model\Costume;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;

class CostumeGender extends AbstractTranslatorHelper
{

	/**
	 * Transforme un texte BBCode en HTML
	 *
	 * @param string $text
	 *      
	 * @return string
	 */
	public function __invoke ($gender)
	{
		$text = '';

		if ($gender == Costume::GENDER_MAN || $gender == Costume::GENDER_MIXED) {
			$text .= '<i class="fa fa-male" title="'.$this->view->escapeHtmlAttr($gender).'"></i>';
		}
		if ($gender == Costume::GENDER_WOMAN || $gender == Costume::GENDER_MIXED) {
			$text .= '<i class="fa fa-female" title="'.$this->view->escapeHtmlAttr($gender).'"></i>';
		}


		return $text;
	}
}
