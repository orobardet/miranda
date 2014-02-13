<?php
namespace Costume\Filter\File;

use Traversable;
use Zend\Filter\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * Attention, cette classe n'est pas utilisable dans une chaine d'input filter dans la validation d'un formulaire :
 * Les formulaires font parfois 2 validations de suite (isValid puis le getData), et la seconde échoue car le fichier a déjà été renommé lors du premier passage.s
 */
class CostumePicture extends \Zend\Filter\AbstractFilter
{

	/**
	 * Largeur maximale
	 *
	 * @var integer
	 */
	protected $maxWidth = null;

	/**
	 * Hauteur maximale
	 *
	 * @var integer
	 */
	protected $maxHeight = null;

	/**
	 * Class constructor
	 *
	 * @param string|array|Traversable $options
	 * @throws Exception\InvalidArgumentException
	 */
	public function __construct($options)
	{
		if ($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray($options);
		} elseif (is_string($options)) {
			$options = array(
				'target' => $options
			);
		} elseif (!is_array($options)) {
			throw new Exception\InvalidArgumentException('Invalid options argument provided to filter');
		}
		$this->setOptions($options);
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return array(
			'max_width' => $this->maxWidth,
			'max_height' => $this->maxHeight
		);
	}

	/**
	 * Returns the files to rename and their new name and location
	 *
	 * @param string|array $options
	 * @return self
	 */
	public function setOptions($options)
	{
		if (!is_array($options) && !$options instanceof Traversable) {
			throw new Exception\InvalidArgumentException(
					sprintf('"%s" expects an array or Traversable; received "%s"', __METHOD__, 
							(is_object($options) ? get_class($options) : gettype($options))));
		}
		
		foreach ($options as $key => $value) {
			switch ($key) {
				case 'max_width':
					$this->maxWidth = (int)$value;
					if ($this->maxWidth <= 0) {
						$this->maxWidth = null;
					}
					break;
					
				case 'max_height':
					$this->maxHeight = (int)$value;
					if ($this->maxHeight <= 0) {
						$this->maxHeight = null;
					}
					break;
			}
		}
		return $this;
	}

	/**
	 * Prépare une image pour l'utiliser dans un costume :
	 *
	 * - La réduit si nécessaire en fonction des options max_height et max_width (en conservant le ratio : réduit le plus grand côté)
	 * - Convertit l'image en jpeg
	 * - La renomme avec un UNIQID.jpg
	 * 
	 * // TODO : tester format de fichier, gérer autre que jpeg
	 *
	 * @param string|array $value Full path of file to change or $_FILES data array
	 * @return string array new filename which has been set
	 */
	public function filter($value)
	{
        // An uploaded file? Retrieve the 'tmp_name'
        $isFileUpload = (is_array($value) && isset($value['tmp_name']));
        if ($isFileUpload) {
            $uploadData = $value;
            $value      = $value['tmp_name'];
        }

		$targetFile = dirname($value) . '/' . uniqid() . '.jpg';
		
		$src = imagecreatefromjpeg($value);
		list($width, $height) = getimagesize($value);
		
		if (($this->maxHeight !== null) && ($this->maxWidth !== null)) {
			$x_ratio = $this->maxWidth / $width;
			$y_ratio = $this->maxHeight / $height;
				
			if (($width <= $this->maxWidth) && ($height <= $this->maxHeight)) {
				$tn_width = $width;
				$tn_height = $height;
			} elseif (($x_ratio * $height) < $this->maxHeight) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $this->maxWidth;
			} else {
				$tn_width = ceil($y_ratio * $width);
				$tn_height = $this->maxHeight;
			}
				
			$dst = imagecreatetruecolor($tn_width, $tn_height);
			imagecopyresampled($dst, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);
				
			imagejpeg($dst, $targetFile);
				
			imagedestroy($dst);
		} else {
			imagejpeg($src, $targetFile);
		}
		
		imagedestroy($src);
		unlink($value);
		
		if ($isFileUpload) {
            $uploadData['tmp_name'] = $targetFile;
            return $uploadData;
        }
		return $targetFile;
	}
}
