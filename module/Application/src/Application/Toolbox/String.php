<?php
namespace Application\Toolbox;

class String
{

	public static function varprintf($string, $vars)
	{
		if (count($vars)) {
			foreach ($vars as $var => $value) {
				$string = str_replace("%$var%", $value, $string);
			}
		}
		return $string;
	}

	public static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_style = STR_PAD_RIGHT, $encoding = "UTF-8")
	{
		return str_pad($input, strlen($input) - mb_strlen($input, $encoding) + $pad_length, $pad_string, $pad_style);
	}
}
?>