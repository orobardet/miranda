<?php
namespace Costume\Model;

use Application\Model\PictureTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class CostumePictureTable extends PictureTable
{

	protected $costumePictureGateway;

	public function __construct(TableGateway $tableGateway, TableGateway $costumePictureGateway)
	{
		$this->costumePictureGateway = $costumePictureGateway;
		parent::__construct($tableGateway);
	}

	/**
	 *
	 * @param int $id
	 * @return \Zend\Db\Sql\Select, \Zend\Db\Sql\Select
	 */
	protected function _getCostumePicturesSelect($id)
	{
		$assocTable = $this->costumePictureGateway->getTable();
		/* @var $sqlSelect Select */
		return $this->tableGateway->getSql()
			->select()
			->join($assocTable, $assocTable . '.picture_id = ' . $this->tableGateway->getTable() . '.id', array(), 'left')
			->where(array(
			'costume_id = ?' => (int)$id
		))
			->order('picture_id');
	}

	/**
	 *
	 * @param integer $id ID du costume dont on veut les images
	 * @throws \Exception
	 * @return \Application\Model\Picture[]
	 */
	public function getCostumePictures($id)
	{
		$resultSet = $this->tableGateway->selectWith($this->_getCostumePicturesSelect($id));
		
		$pictures = array();
		if (count($resultSet)) {
			foreach ($resultSet as $picture) {
				$pictures[] = $picture;
			}
		}
		
		return $pictures;
	}

	/**
	 * @param integer $id
	 * @return \Application\Model\Picture
	 */
	public function getFirstCostumePicture($id)
	{
		$select = $this->_getCostumePicturesSelect($id);
		$select->limit(1);
		
		$resultSet = $this->tableGateway->selectWith($select);
		
		return $resultSet->current();
	}

	public function saveCostumePictures(Costume $costume)
	{
		$costumeId = $costume->getId();
		$currentPictures = $this->getCostumePictures($costumeId);
		$pictures = $costume->getPictures();
		
		$currentPicturesIds = array();
		$picturesIds = array();
		
		if (count($currentPictures)) {
			foreach ($currentPictures as $picture) {
				$currentPicturesIds[] = $picture->getId();
			}
		}
		if (count($pictures)) {
			foreach ($pictures as $picture) {
				$pictureId = $picture->getId();
				// Si l'image n'a pas d'ID, on l'enregistre d'abord
				if (!$pictureId) {
					$this->savePicture($picture);
					$pictureId = $picture->getId();
				}
				$picturesIds[] = $pictureId;
			}
		}
		
		// On retire tous les ID qui ne sont plus dans la liste actuelle
		$removedPictures = array_diff($currentPicturesIds, $picturesIds);
		if (count($removedPictures)) {
			foreach ($removedPictures as $id) {
				$this->costumePictureGateway->delete(array(
					'costume_id' => $costumeId,
					'picture_id' => $id
				));
				$this->deletePicture($id);
			}
		}
		
		// On ajoute tous les ID qui sont nouveaux dans la liste actuelle
		$newPictures = array_diff($picturesIds, $currentPicturesIds);
		if (count($newPictures)) {
			foreach ($newPictures as $id) {
				$this->costumePictureGateway->insert(array(
					'costume_id' => $costumeId,
					'picture_id' => $id
				));
			}
		}
	}

	public function deleteCostumePictures(Costume $costume)
	{
		$costumeId = $costume->getId();
		$pictures = $this->getCostumePictures($costumeId);
		
		if (count($pictures)) {
			foreach ($pictures as $picture) {
				$pictureId = $picture->getId();
				$this->costumePictureGateway->delete(array(
					'picture_id' => $pictureId
				));
				$this->deletePicture($pictureId);
			}
		}
	}
}
