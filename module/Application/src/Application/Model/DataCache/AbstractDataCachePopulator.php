<?php
namespace Application\Model\DataCache;

abstract class AbstractDataCachePopulator
{

	protected $_cachedCollections;
	protected $_cacheStatus;
	
	protected function addCachedCollection($object, $name = null)
	{
		if ($object instanceof DataCacheAwareInterface) {
			if (!is_array($this->_cachedCollections)) {
				$this->_cachedCollections = array();
				$this->_cacheStatus = array();
			}
			if (!$name) {
				$name = uniqid();
			}
			$this->_cachedCollections[$name] = $object;
			$this->_cacheStatus[$name] = false;
		}
		
		return $name;
	}

	protected function removeCachedCollection($name)
	{
		if (is_array($this->_cachedCollections) && array_key_exists($name, $this->_cachedCollections)) {
			unset($this->_cachedCollections[$name]);
		}
	}

	public function populateCaches()
	{
		if (count($this->_cachedCollections)) {
			foreach ($this->_cachedCollections as $name => $object) {
				if (array_key_exists($name, $this->_cacheStatus) && !$this->_cacheStatus[$name]) {
					$object->populateCache();
					$this->_cacheStatus[$name] = true;
				}
			}
		}
	}

	public function __destruct()
	{
		$this->_cachedCollections = null;
	}
}
