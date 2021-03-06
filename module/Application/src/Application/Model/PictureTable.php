<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class PictureTable extends Picture
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}
	
	/**
	 * Retourne un nouvel objet Picture pour les costumes
	 *
	 * (avec les bons root path et url root déjà défini)
	 *
	 * @return \Application\Model\Picture
	 */
	public function pictureFactory()
	{
		$picturePrototype = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
		$newPicture = clone $picturePrototype;
		return $newPicture;
	}

	/**
	 * Retoune toutes les images existantes
	 *
	 * @return Picture[] Liste des images (sous forme d'un iterable)
	 */
	public function fetchAll()
	{
		return $this->tableGateway->select();
	}

	public function getPicture($id, $exceptionIfNone = true)
	{
		$id = (int)$id;
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$row = $rowset->current();
		if (!$row) {
			if ($exceptionIfNone) {
				throw new \Exception("Could not find picture $id");
			} else {
				return false;
			}
		}
		
		return $row;
	}
	
	public function savePicture(Picture $picture)
	{
		$data = $picture->getArrayCopy();
		
		$id = (int)$picture->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
			$picture->setId($id);
		} else {
			if ($this->getPicture($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("Picture id $id does not exist");
			}
		}
	}

	public function deletePicture($id)
	{
		$picture = $this->getPicture($id, false);
		if ($picture) {
			$picture->deleteFile();
		}
		$this->tableGateway->delete(array(
			'id' => $id
		));
	}
}
