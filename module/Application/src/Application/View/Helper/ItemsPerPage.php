<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Model\Paginator\ItemsPerPageManager;

class ItemsPerPage extends AbstractHelper
{

	/**
	 * Manager d'item par page
	 *
	 * @var \Application\Model\Paginator\ItemsPerPageManager
	 */
	protected $manager;

	public function __construct(ItemsPerPageManager $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * Accès au manager d'item par page
	 */
	public function __invoke ()
	{
		return $this->manager;
	}
}
