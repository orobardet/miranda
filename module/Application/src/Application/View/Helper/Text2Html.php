<?php
namespace Application\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;

class Text2Html extends AbstractTranslatorHelper
{

	/**
	 * Transforme un texte BBCode en HTML
	 *
	 * @param string $text
	 *      
	 * @return string
	 */
	public function __invoke ($text)
	{
		$text = $this->view->escapeHtml($text);
		
		$text = str_replace("\n", "<br/>", $text);
		
		return $text;
	}
}
