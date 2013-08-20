<?php
namespace Application\Toolbox;

class String
{

	public static function varprintf ($string, $vars)
	{
		if (count($vars)) {
			foreach ($vars as $var => $value) {
				$string = str_replace("%$var%", $value, $string);
			}
		}
		return $string;
	}
}
?>