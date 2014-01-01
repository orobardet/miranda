<?php
namespace Application\Model;

class ObjectModelBase
{

	protected $baseAttributes = array();

	public function filterDbId($value)
	{
		$filtered = filter_var($value, FILTER_VALIDATE_INT, array(
			'min_range' => 1,
			'default' => null
		));
		if ($filtered === false) {
			return null;
		}
		
		return $filtered;
	}

	public function setBaseAttribute($name, $value)
	{
		$this->baseAttributes[$name] = $value;
	}

	public function hasBaseAttribute($name)
	{
		return array_key_exists($name, $this->baseAttributes);
	}

	public function getBaseAttribute($name, $default = null)
	{
		if (array_key_exists($name, $this->baseAttributes)) {
			return $this->baseAttributes[$name];
		} else {
			return $default;
		}
	}

	public function unsetBaseAttribute($name)
	{
		if (array_key_exists($name, $this->baseAttributes)) {
			unset($this->baseAttributes[$name]);
		}
	}

	/**
	 * Retourne une date stockée sous forme de timestampt, en lui appliquant un éventuelle formatage
	 *
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date()
	 * et selon le format du paramètre $format.
	 *
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé,
	 * retourne la chaine "N/A".
	 *
	 * __Exemples :__
	 *
	 * ~~~~~~~~
	 * $this->getFormatedDate(); // Retourne le timestamp
	 * $this->getFormatedDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param int $ts Le timestamp représentant la date.
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string int
	 */
	protected function getFormatedDate($ts, $format = null)
	{
		if (!$format) {
			return $ts;
		} else {
			if ($ts > 0) {
				return date($format, $ts);
			} else {
				return "N/A";
			}
		}
	}
}
