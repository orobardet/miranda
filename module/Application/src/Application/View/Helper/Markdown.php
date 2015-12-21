<?php
namespace Application\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;

class Markdown extends AbstractTranslatorHelper
{

	/**
	 * Transforme un texte Markdown en HTML
	 *
	 * @param string $text
	 *      
	 * @return string
	 */
	public function __invoke ($text, $withEncloseTag = false)
	{
		$markdown = str_replace("\n", "", \Michelf\Markdown::defaultTransform($text));

        if ($withEncloseTag) {
            return '<div class="markdown">'.$markdown.'</div>';
        } else {
            return $markdown;
        }
	}
}
