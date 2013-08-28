<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\View\Helper;

use Zend\I18n\Exception;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Application\Toolbox\String as StringTools;

/**
 * View helper for translating messages.
 */
class TranslateReplace extends AbstractTranslatorHelper
{

	/**
	 * Translate a message and replace variables by given values
	 *
	 * @param string $message     
	 * @param array $vars Associative array containing vars to replace   	
	 * @param string $textDomain        	
	 * @param string $locale        	
	 * @throws Exception\RuntimeException
	 * @return string
	 */
	public function __invoke ($message, $vars = array(), $textDomain = null, $locale = null)
	{
		$translator = $this->getTranslator();
		if (null === $translator) {
			throw new Exception\RuntimeException('Translator has not been set');
		}
		if (null === $textDomain) {
			$textDomain = $this->getTranslatorTextDomain();
		}
		
		return StringTools::varprintf($translator->translate($message, $textDomain, $locale), $vars);
	}
}
