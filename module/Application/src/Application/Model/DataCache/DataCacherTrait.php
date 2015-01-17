<?php
namespace Application\Model\DataCache;

trait DataCacherTrait
{

	static protected $_cacheData;
	
	static protected $_cacheComplete = false;

	protected function dataCacheComplete()
	{
		self::$_cacheComplete = true;
	}
	
	protected function dataCacheIsComplete()
	{
		return self::$_cacheComplete;
	}
	
	protected function dataCacheCount()
	{
		if (is_array(self::$_cacheData)) {
			return count(self::$_cacheData);
		}
		return false;
	}
	
	protected function dataCacheGetAll()
	{
		if (is_array(self::$_cacheData)) {
			return self::$_cacheData;
		}
		return array();
	}
	
	protected function dataCacheAdd($key, $value)
	{
		if (!is_array(self::$_cacheData)) {
			self::$_cacheData = array();
		}
		self::$_cacheData[$key] = $value;
	}

	protected function dataCacheGet($key)
	{
		if (is_array(self::$_cacheData) && array_key_exists($key, self::$_cacheData)) {
			return self::$_cacheData[$key];
		}
		
		return null;
	}

	protected function dataCacheIs($key)
	{
		if (is_array(self::$_cacheData)) {
			return array_key_exists($key, self::$_cacheData);
		}
		
		return false;
	}

	protected function dataCacheRemove($key)
	{
		if (array_key_exists($key, self::$_cacheData)) {
			unset(self::$_cacheData[$key]);
			self::$_cacheComplete = false;
		}
	}

	protected function dataCacheClear()
	{
		self::$_cacheData = array();
		self::$_cacheComplete = false;
	}
}
