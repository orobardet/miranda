<?php
namespace Application\Model\DataCache;

abstract class AbstractDataCacher
{

	protected $_cacheData;

	protected function dataCacheAdd($key, $value)
	{
		if (!is_array($this->_cacheData)) {
			$this->_cacheData = array();
		}
		$this->_cacheData[$key] = $value;
	}

	protected function dataCacheGet($key)
	{
		if (is_array($this->_cacheData) && array_key_exists($key, $this->_cacheData)) {
			return $this->_cacheData[$key];
		}
		
		return null;
	}

	protected function dataCacheIs($key)
	{
		if (is_array($this->_cacheData)) {
			return array_key_exists($key, $this->_cacheData);
		}
		
		return false;
	}

	protected function dataCacheRemove($key)
	{
		if (array_key_exists($key, $this->_cacheData)) {
			unset($this->_cacheData[$key]);
		}
	}

	protected function dataCacheClear()
	{
		$this->_cacheData = array();
	}
}