<?php
namespace Costume\Controller;

use Costume\Model\Import\OldExcel as OldExcelImport;
use Acl\Controller\AclConsoleControllerInterface;
use Costume\Model\Costume;

class ConsoleController extends AbstractCostumeController  implements AclConsoleControllerInterface
{

	public function aclConsoleIsAllowed($action)
	{
		return true;
	}

	public function importAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$csvFile = $this->getRequest()->getParam('csv_file', null);
		$pictureDir = $this->getRequest()->getParam('picture-dir', null);
		$logFile = $this->getRequest()->getParam('log-file', '/dev/null');
		$errorFile = $this->getRequest()->getParam('error-file', '/dev/null');
		
		if (!is_file($csvFile) || !is_readable($csvFile)) {
			$this->console()->writeLine("$csvFile is not a valid or readable file");
			return;
		}
		
		$logHandle = fopen($logFile, 'w');
		$errorHandle = fopen($errorFile, 'w');
		
		$importer = new OldExcelImport();
		
		$costumesTab = $importer->readCsvFile($csvFile, $logHandle, $errorHandle);
		
		$this->console()->writeLine("Costume lus : " . count($costumesTab));
		fwrite($logHandle, "----- Debut d'enregistrement des costumes ---------------\n");
		$costumeImported = 0;
		if (count($costumesTab)) {
			$this->console()->write("Importation des costumes : 0...");
			foreach ($costumesTab as $costumeLine) {
				if (count($costumeLine) > 2) {
					// Lecture du code
					$code = trim($costumeLine[2]);
					if (empty($code)) {
						fwrite($errorHandle, "Non importé : code vide pour ".join(' | ', $costumeLine)."\n");
						continue;
					}
					
					if (strlen($code > 20)) {
						fwrite($errorHandle, "Non importé : code '$code' trop long\n");
						continue;
					}
					
					if ($this->getCostumeTable()->getCostumeByCode($code, false)) {
						fwrite($errorHandle, "Non importé : le code '$code' existe déjà pour ".join(' | ', $costumeLine)."\n");
						continue;
					}
					
					$costume = new Costume();
					$costume->setCode($code);
						
					// Libellé et description
					$label = $costumeLine[0];
					$descr = "";
					if (strlen($label) > 255) {
						$descr = $label."\n";
						$label = substr($label, 0, 255);
					}
					$descr .= $costumeLine[10];
					$costume->setLabel($label);
					$costume->setDescr($descr);
								
					// Détection du genre
					$genderRaw = str_split(preg_replace('/[^hf]/', '', strtolower($costumeLine[3])));
					if (in_array('f', $genderRaw) && in_array('h', $genderRaw)) {
						$costume->setGender(Costume::GENDER_MIXED);
					} else if (in_array('f', $genderRaw)) {
						$costume->setGender(Costume::GENDER_WOMAN);
					} else if (in_array('h', $genderRaw)) {
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
					$pictureFile = 'none';
					if (is_file($pictureDir . '/' . $code . '.jpg')) {
						$pictureFile = $pictureDir . '/' . $code . '.jpg';
					} else {
						// On tente en enlevant la dernière partie après un . du code (qui pourrait être un numéro si quantité de l'article > 1)
						$tmpCode = preg_replace('/\.[^\.]*$/', '', $code);
						if (is_file($pictureDir . '/' . $tmpCode . '.jpg')) {
							$pictureFile = $pictureDir . '/' . $tmpCode . '.jpg';
						} else {
							fwrite($errorHandle, "Pas de fichier image trouvé pour $code\n");
						}
					}
				} else {
					fwrite($errorHandle, "Pas assez de colonnes pour " . join(' | ', $costumeLine) . "\n");
				}

				// Sauvegarde du costume
				$this->getCostumeTable()->saveCostume($costume);
				
				$costumeImported++;
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
