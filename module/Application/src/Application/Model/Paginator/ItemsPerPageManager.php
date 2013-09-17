<?php
namespace Application\Model\Paginator;

use Zend\Session\Storage\StorageInterface;

class ItemsPerPageManager
{
	protected static $_defaultItemsPerPage = array(
		5 => '5',
		10 => '10',
		15 => '15',
		20 => '20',
		25 => '25',
		50 => '50',
		75 => '75',
		100 => '100',
		null => 'All'		
	);
	
	protected $contextItemsPerPage = array();
	
	/**
	 * Objet de session
	 *
	 * @var Zend\Session\Storage\StorageInterface
	 */
	protected $session;
	
	public function __construct(StorageInterface $session)
	{
		$this->session = $session;
		if (!isset($this->session->itemsPerPage)) {
			$this->session->itemsPerPage = array();
		}
	}
	
	public function setItemsPerPage($context, $value = null)
	{
		if (($value === null) && array_key_exists($context, $this->session->itemsPerPage)) {
			unset($this->session->itemsPerPage[$context]);
			return;
		}
		if ($value !== null) {
			$this->session->itemsPerPage[$context] = $value;
		}
	}
	
	public function getItemsPerPage($context, $default)
	{
		if (array_key_exists($context, $this->session->itemsPerPage)) {
			return $this->session->itemsPerPage[$context];
		}
		return $default;
	}
	
	public function setItemsPerPageList($context, $list = null)
	{
		if (($list === null) && array_key_exists($context, $this->contextItemsPerPage)) {
			unset($this->contextItemsPerPage[$context]);
			return;
		}
		if (is_array($list)) {
			$this->contextItemsPerPage[$context] = $list;
		}
	}
	
	
	public function getItemsPerPageList($context = null, $default = null)
	{
		if ($context === null) {
			return static::$_defaultItemsPerPage;
		}
		
		if (array_key_exists($context, $this->contextItemsPerPage)) {
			return $this->contextItemsPerPage[$context];
		} else if (is_array($default)) {
			return $default;
		} else {
			return static::$_defaultItemsPerPage;
		}
	}
}

?>