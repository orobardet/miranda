<?php
namespace Costume\Controller;

use Costume\Model\Import\OldExcel as OldExcelImport;
use Acl\Controller\AclConsoleControllerInterface;
use Costume\Model\Costume;
use Costume\Model\Color;
use Costume\Model\Tag;

class ConsoleController extends AbstractCostumeController implements AclConsoleControllerInterface
{

	public function aclConsoleIsAllowed($action)
	{
		return true;
	}

	public function importAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		$config = $this->getServiceLocator()->get('Miranda\Service\Config');
		/* @var $pictureTable \Application\Model\PictureTable */
		$pictureTable = $this->getServiceLocator()->get('Miranda\Model\PictureTable');
		$costumePictureTable = $this->getServiceLocator()->get('Costume\Model\CostumePictureTable');
		$colorTable = $this->getServiceLocator()->get('Costume\Model\ColorTable');
		
		$csvFile = $this->getRequest()->getParam('csv_file', null);
		$pictureDir = $this->getRequest()->getParam('picture-dir', null);
		$tags = $this->getRequest()->getParam('tags', null);
		$logFile = $this->getRequest()->getParam('log-file', '/dev/null');
		$errorFile = $this->getRequest()->getParam('error-file', '/dev/null');
		
		$globalTags = array();
		$tagsList = explode(',', $tags);
		if (count($tagsList)) {
			foreach ($tagsList as $tag) {
				$tag = trim($tag);
				if ($tag != '') {
					$globalTags[] = new Tag(ucfirst(strtolower($tag)));
				}
			}
		}
		
		if (!is_file($csvFile) || !is_readable($csvFile)) {
			$this->console()->writeLine("$csvFile is not a valid or readable file");
			return;
		}
		
		$logHandle = fopen($logFile, 'w');
		$errorHandle = fopen($errorFile, 'w');
		
		$importer = new OldExcelImport();
		
		$importer->readCsvFile($csvFile, $logHandle, $errorHandle);
		
		$this->console()->writeLine("Costume lus : " . count($importer));
		fwrite($logHandle, "----- Debut d'enregistrement des costumes ---------------\n");
		$costumeImported = 0;
		if (count($importer)) {
			$this->console()->write("Importation des costumes : 0...");
			foreach ($importer as $costumeLine) {
				if (count($costumeLine) > 2) {
					// Lecture du code
					$code = trim($costumeLine[2]);
					if (empty($code)) {
						fwrite($errorHandle, "Non importé : code vide pour " . join(' | ', $costumeLine) . "\n");
						continue;
					}
					
					if (strlen($code > 20)) {
						fwrite($errorHandle, "Non importé : code '$code' trop long\n");
						continue;
					}
					
					$costume = $this->getCostumeTable()->getCostumeByCode($code, false);
					if ($costume) {
						fwrite($logHandle, "Mise à jour : le code '$code' existe déjà pour " . join(' | ', $costumeLine) . "\n");
					} else {
						$costume = new Costume();
						$costume->setCode($code);
					}
					
					// Libellé et description
					$label = $costumeLine[0];
					$descr = "";
					if (strlen($label) > 255) {
						$descr = $label . "\n";
						$label = substr($label, 0, 255);
					}
					$descr .= $costumeLine[10];
					$costume->setLabel($label);
					$costume->setDescr($descr);
					
					// Détection du genre
					$genderRaw = str_split(preg_replace('/[^hf]/', '', strtolower($costumeLine[3])));
					if (in_array('f', $genderRaw) && in_array('h', $genderRaw)) {
						$costume->setGender(Costume::GENDER_MIXED);
					} else 
						if (in_array('f', $genderRaw)) {
							$costume->setGender(Costume::GENDER_WOMAN);
						} else 
							if (in_array('h', $genderRaw)) {
								$costume->setGender(Costume::GENDER_MAN);
							} else {
								$costume->setGender(Costume::GENDER_NONE);
							}
					
					// Détection de la taille du costume
					if (!empty($costumeLine[5])) {
						$costume->setSize(substr($costumeLine[5], 0, 20));
					}
					
					// Détection de l'état du costume
					if (!empty($costumeLine[11])) {
						$costume->setState(substr($costumeLine[11], 0, 255));
					}
					
					// Détection de quantité de costume
					$quantity = intval($costumeLine[4]);
					if (!empty($quantity)) {
						$costume->setQuantity($quantity);
					} else {
						$costume->setQuantity(1);
					}
					
					// Détection de l'image
					$picturePath = null;
					$pictureSource = null;
					if (is_file($pictureDir . '/' . $code . '.jpg')) {
						$picturePath = $code . '.jpg';
						$pictureSource = $pictureDir . '/' . $picturePath;
					} else {
						// On tente en enlevant la dernière partie après un . du code (qui pourrait être un numéro si quantité de l'article > 1)
						$tmpCode = preg_replace('/\.[^\.]*$/', '', $code);
						if (is_file($pictureDir . '/' . $tmpCode . '.jpg')) {
							$picturePath = $tmpCode . '.jpg';
							$pictureSource = $pictureDir . '/' . $picturePath;
						} else {
							fwrite($errorHandle, "Pas de fichier image trouvé pour $code\n");
						}
					}
					if ($pictureSource && $picturePath) {
						// Image trouvée, on l'utilise
						
						// Est-ce que le costume dispose déjà de la même image ?
						$currentPictures = $costume->getPictures();
						$alreadyExists = false;
						if (count($currentPictures)) {
							foreach ($currentPictures as $currentPicture) {
								if ($currentPicture->getPath() == $picturePath) {
									$alreadyExists = true;
									// Mise à jour du fichier image
									$currentPicture->copyFromFile($pictureSource);
									break;
								}
							}
						}
						if (!$alreadyExists) {
							$picture = $costumePictureTable->pictureFactory();
							$picture->setPath($picturePath);
							if ($picture->copyFromFile($pictureSource)) {
								$costume->addPicture($picture);
							}
						}
					}
					
					// Détection des couleurs
					$colors = explode('+', $costumeLine[6]);
					if (count($colors)) {
						$color = ucfirst(strtolower(trim(array_shift($colors))));
						if ($color != '') {
							if (count($colors)) {
								fwrite($errorHandle, 
										"Précisions de couleur principale non importé pour " . $costume->getCode() . " : +" . join('+', $colors) . "\n");
							}
							// Si la couleur existe déjà en BDD, on l'utilise
							$colorObject = $colorTable->getColorByName($color, true, false);
							// Sinon on la crée
							if (!$colorObject) {
								$colorObject = new Color();
								$colorObject->setName($color);
								$colorObject->setColorCode('FFFFFF');
								$colorTable->saveColor($colorObject);
								fwrite($errorHandle, "Ajout d'une nouvelle couleur pour " . $costume->getCode() . " : $color\n");
							}
							$costume->setPrimaryColor($colorObject);
						}
					}
					
					$colors = explode('+', $costumeLine[7]);
					if (count($colors)) {
						$color = ucfirst(strtolower(trim(array_shift($colors))));
						if ($color != '') {
							if (count($colors)) {
								fwrite($errorHandle, 
										"Précisions de couleur secondaire non importé pour " . $costume->getCode() . " : +" . join('+', $colors) . "\n");
							}
							// Si la couleur existe déjà en BDD, on l'utilise
							$colorObject = $colorTable->getColorByName($color, true, false);
							// Sinon on la crée
							if (!$colorObject) {
								$colorObject = new Color();
								$colorObject->setName($color);
								$colorObject->setColorCode('FFFFFF');
								$colorTable->saveColor($colorObject);
								fwrite($errorHandle, "Ajout d'une nouvelle couleur pour " . $costume->getCode() . " : $color\n");
							}
							$costume->setSecondaryColor($colorObject);
						}
					}
					
					// Ajout des tags
					$costume->setTags(array_unique(array_merge($globalTags, $importer->localTags)));
						
					// Sauvegarde du costume
					$this->getCostumeTable()->saveCostume($costume);
					
					$costumeImported++;
				} else {
					fwrite($errorHandle, "Pas assez de colonnes pour " . join(' | ', $costumeLine) . "\n");
				}
				
				$this->console()->clearLine();
				$this->console()->write("Importation des costumes : $costumeImported...");
			}
			$this->console()->clearLine();
			$this->console()->writeLine("$costumeImported costume(s) importé(s) !");
		} else {
			$this->console()->writeLine('Aucun costume à importer.');
		}
		fwrite($logHandle, "\n$costumeImported costume(s) importé(s)\n");
		fwrite($logHandle, "----- Fin d'enregistrement des costumes ---------------\n");
		
		return;
	}

	public function preparepicturesAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$inputDir = $this->getRequest()->getParam('input_dir', '');
		$outputDir = $this->getRequest()->getParam('output_dir', '');
		
		if (!is_dir($inputDir) || !is_readable($inputDir)) {
			$this->console()->writeLine("$inputDir is not a valid or readable directory");
			return;
		}
		if (!is_dir($outputDir) || !is_writable($outputDir)) {
			$this->console()->writeLine("$outputDir is not a valid or writable directory");
			return;
		}
		
		$this->console()->writeLine();
		$this->console()->writeLine("Input dir : $inputDir");
		$this->console()->writeLine("Output dir : $outputDir");
		$this->console()->writeLine();
		
		$importer = new OldExcelImport();
		
		$this->console()->write("Cleaning output directory... ");
		$importer->deleteDirectoyContent($outputDir);
		$this->console()->writeLine("OK");
		
		$this->console()->write("Scanning input directory for jpeg files... ");
		$scannedPicFile = $importer->searchFiles($inputDir, '/\.jpe?g$/i');
		$scannedFileCount = count($scannedPicFile);
		$this->console()->writeLine("OK, $scannedFileCount picture files found");
		
		if (count($scannedPicFile)) {
			$fileCount = 1;
			foreach ($scannedPicFile as $picture) {
				$this->console()->clearLine();
				$percent = round(($fileCount - 1) * 100 / $scannedFileCount);
				$picName = pathinfo($picture, PATHINFO_BASENAME);
				$this->console()->write("Preparing picture $picName - $fileCount / $scannedFileCount - $percent%...");
				$importer->resizeAndSavePicture($picture, $outputDir, $this->config->costume->pictures->get('max_width', null), 
						$this->config->costume->pictures->get('max_height', null));
				$fileCount++;
			}
		}
		$this->console()->clearLine();
		$this->console()->writeLine("All pictures prepared in $outputDir !");
		
		return;
	}
}
