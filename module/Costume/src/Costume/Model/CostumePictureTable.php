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
	 * @param integer $id ID du costume dont on veut les images
	 * @throws \Exception
	 * @return boolean Picture[]
	 */
	public function getCostumePictures($id)
	{
		$assocTable = $this->costumePictureGateway->getTable();
		/* @var $sqlSelect Select */
		$sqlSelect = $this->tableGateway->getSql()->select();
		$sqlSelect->join($assocTable, $assocTable . '.picture_id = ' . $this->tableGateway->getTable() . '.id', array(), 'left');
		$sqlSelect->where(array(
			'costume_id = ?' => $id
		));
		
		$resultSet = $this->tableGateway->selectWith($sqlSelect);
		
		return $resultSet;
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
				$this->costumePictureGateway->delete(array('picture_id' => $id));
				$this->deletePicture($id);
			}
		}
		
		// On ajoute tous les ID qui sont nouveaux dans la liste actuelle
		$newPictures = array_diff($picturesIds, $currentPicturesIds);
		if (count($newPictures)) {
			foreach ($newPictures as $id) {
				$this->costumePictureGateway->insert(array('costume_id' => $costumeId, 'picture_id' => $id));
			}
		}
	}
}
