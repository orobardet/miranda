<?php
namespace Application\Model;

trait FeaturesTrait
{

	protected static $features = array();

	public function setFeatures(array $features, $state = true)
	{
		if (!is_array(self::$features)) {
			self::$features = array();
		}
		
		if (count($features)) {
			foreach ($features as $feature) {
				self::$features[$feature] = (boolean)$state;
			}
		}
	}

	public function getFeatures()
	{
		return self::$features;
	}

	public function enableFeatures(array $features)
	{
		$this->setFeatures($features, true);
	}

	public function disableFeatures(array $features)
	{
		$this->setFeatures($features, false);
	}

	public function hasFeature($feature)
	{
		if (!is_array(self::$features)) {
			self::$features = array();
		}
		
		if (count(self::$features) && array_key_exists($feature, self::$features) && self::$features[$feature]) {
			return true;
		}
		
		return false;
	}

	public function hasFeatures(array $features)
	{
		if (!is_array(self::$features)) {
			self::$features = array();
		}
		
		if (count(self::$features) && count($features)) {
			foreach ($features as $feature) {
				if (array_key_exists($feature, self::$features) && self::$features[$feature]) {
					return true;
				}
			}
		}
		
		return false;
	}
}
