<?php
namespace Application\Model;

class Picture extends ObjectModelBase
{

	const ORIENTATION_PORTRAIT = 1;

	const ORIENTATION_LANDSCAPE = 2;

	/**
	 * ID en BDD du costume
	 *
	 * @var integer
	 */
	protected $id;

	/**
	 * Chemin d'accès à l'image relative à la racine de stockage ou d'accès
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Largeur en pixel de l'image
	 *
	 * @var integer
	 */
	protected $width;

	/**
	 * Hauteur en pixel de l'image
	 *
	 * @var integer
	 */
	protected $height;

	/**
	 * Chemin d'accès absolu racine où se trouve l'image.
	 * Chemin complet du fichier image = $strorage_root/$path
	 *
	 * @var string
	 */
	protected $storage_root;

	/**
	 * URL racine d'accès à l'image.
	 * URL d'accès = $url_path/$path
	 *
	 * @var string
	 */
	protected $url_root;

	/**
	 *
	 * @return $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return $path
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 *
	 * @return $width
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 *
	 * @return $height
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 *
	 * @return $storage_root
	 */
	public function getStorageRoot()
	{
		return $this->storage_root;
	}

	/**
	 *
	 * @return $url_root
	 */
	public function getUrlRoot()
	{
		return $this->url_root;
	}

	/**
	 *
	 * @param number $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 *
	 * @param string $path
	 */
	public function setPath($path)
	{
		$this->path = ltrim($path, '/' . DIRECTORY_SEPARATOR);
	}

	/**
	 *
	 * @param number $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 *
	 * @param number $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 *
	 * @param string $storage_root
	 */
	public function setStorageRoot($storage_root)
	{
		$this->storage_root = ($storage_root == DIRECTORY_SEPARATOR) ? $storage_root : rtrim($storage_root, DIRECTORY_SEPARATOR);
	}

	/**
	 *
	 * @param string $url_root
	 */
	public function setUrlRoot($url_root)
	{
		$this->url_root = ($url_root == '/') ? $url_root : rtrim($url_root, '/');
	}

	/**
	 * Retourne le chemin d'accès complet à l'image
	 *
	 * @return string Chemin d'accès complet à l'image
	 */
	public function getStoragePath()
	{
		return $this->storage_root . DIRECTORY_SEPARATOR . $this->path;
	}

	/**
	 * Retourne l'URL d'accès à l'image
	 *
	 * @return string URL d'accès à l'image
	 */
	public function getUrlPath()
	{
		return $this->url_root . '/' . $this->path;
	}

	/**
	 * Retourne l'orientation de l'image
	 *
	 * L'image peut être en portrait si la largeur est inférieur ou égale à la hauteur, sinon en paysage.
	 *
	 * @return int L'orientation de l'image : ::ORIENTATION_PORTRAIT ou ::ORIENTATION_LANDSCAPE
	 */
	public function getOrientation()
	{
		if ($this->width <= $this->height) {
			return static::ORIENTATION_PORTRAIT;
		} else {
			return static::ORIENTATION_LANDSCAPE;
		}
	}

	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->path = (array_key_exists('path', $data)) ? $data['path'] : null;
		$this->width = (array_key_exists('width', $data)) ? $data['width'] : null;
		$this->height = (array_key_exists('height', $data)) ? $data['height'] : null;
	}

	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'path' => $this->path,
			'width' => $this->width,
			'height' => $this->height
		);
	}
}