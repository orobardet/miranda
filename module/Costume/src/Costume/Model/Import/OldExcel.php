<?php
namespace Costume\Model\Import;

use Costume\Model\Tag;
class OldExcel implements \Iterator, \Countable 
{
	public $localTags;
	protected $fileData;

	public function count()
	{
		return count($this->fileData);
	}
	
	public function rewind() {
		$line = reset($this->fileData);
	}
	
	public function current() {
		$line = current($this->fileData);
		if ($line) {
			$this->localTags = $line->tags;
			return $line->data;
		}
	
		return false;
	}
	
	public function key() {
		return key($this->fileData);
	}
	
	public function next() {
		$line = next($this->fileData);
		if ($line) {
			$this->localTags = $line->tags;
			return $line->data;
		}
	
		return false;
	}
	
	public function valid() {
		return current($this->fileData)?true:false;
	}
	
	public function readCsvFile($file, $logFile, $errorFile)
	{
		$this->fileData = array();
		$this->costumesCount = 0;
		
		fwrite($logFile, "----- Debut de lecture du fichier CSV $file ---------------\n");
		fwrite($errorFile, "----- Debut de lecture du fichier CSV $file ---------------\n");
		
		$localTags = array();
		$handle = fopen($file, "r");
		if ($handle) {
			$row = 1;
			$line = fgets($handle);
			while ($line) {
				$line = rtrim($line, "\r\n");
				
				// Si la ligne commence par #####, fin de chargement du fichier
				if (preg_match('/^"?#####/u', $line)) {
					fwrite($logFile, "Ligne de fin de ficher (#$row) : $line\n");
					$line = false;
					continue;
				}
				
				// Si la ligne commence par !, c'est une nouvelle section à utiliser comme tag
				if (preg_match('/^"?!/u', $line)) {
					$tags = str_getcsv($line, ';');
					
					if (count($tags)) {
						// Retrait du ! dans le premier tag
						$tags[0] = ltrim($tags[0], '!');
						$localTags = array();
						foreach ($tags as $tag) {
							$tag = trim($tag);
							if ($tag != '') {
								$localTags[] = new Tag(ucfirst(strtolower($tag)));
							}
						}
					}
					
					fwrite($logFile, "Nouveaux tags locaux (#$row) : ".join(',', $tags)."\n");
					$line = fgets($handle);
					$row++;
					continue;
				}
				// Si la ligne commence par #, on l'ignore
				if (preg_match('/^"?#/u', $line)) {
					fwrite($logFile, "Ligne ignorée (#$row) : $line\n");
					$line = fgets($handle);
					$row++;
					continue;
				}
				// Si la ligne est vide (ou ne contient que des champs vides), on l'ignore
				if (preg_match('/^[;\s]*$/u', $line)) {
					fwrite($logFile, "Ligne vide (#$row) : $line\n");
					$line = fgets($handle);
					$row++;
					continue;
				}
				
				$tabLine = str_getcsv($line, ';');
				$lineObject = new \stdClass();
				$lineObject->data = $tabLine;
				$lineObject->tags = $localTags;
				$this->fileData[] = $lineObject;
				
				$line = fgets($handle);
				$row++;
			}
			fclose($handle);
		}
		fwrite($logFile, "----- Fin de lecture du fichier CSV $file ---------------\n\n");
		fwrite($errorFile, "----- Fin de lecture du fichier CSV $file ---------------\n\n");
	}

	public function deleteDirectoyContent($dir)
	{
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), 
				\RecursiveIteratorIterator::CHILD_FIRST);
		
		foreach ($files as $fileinfo) {
			if ($fileinfo->isDir()) {
				rmdir($fileinfo->getRealPath());
			} else {
				unlink($fileinfo->getRealPath());
			}
		}
	}

	public function searchFiles($dir, $pattern)
	{
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS));
		
		$findedFiles = array();
		foreach ($files as $fileinfo) {
			if ($fileinfo->isFile()) {
				if (preg_match($pattern, $fileinfo->getFilename())) {
					$findedFiles[] = $fileinfo->getRealPath();
				}
			}
		}
		
		return $findedFiles;
	}

	public function resizeAndSavePicture($pictureFile, $outputDir, $maxWidth = null, $maxHeight = null)
	{
		$targetFile = $outputDir . '/' . pathinfo($pictureFile, PATHINFO_FILENAME) . '.jpg';
		
		$src = imagecreatefromjpeg($pictureFile);
		list($width, $height) = getimagesize($pictureFile);
		
		if (($maxHeight !== null) && ($maxWidth !== null)) {
			
			$x_ratio = $maxWidth / $width;
			$y_ratio = $maxHeight / $height;
			
			if (($width <= $maxWidth) && ($height <= $maxHeight)) {
				$tn_width = $width;
				$tn_height = $height;
			} elseif (($x_ratio * $height) < $maxHeight) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $maxWidth;
			} else {
				$tn_width = ceil($y_ratio * $width);
				$tn_height = $maxHeight;
			}
			
			$dst = imagecreatetruecolor($tn_width, $tn_height);
			imagecopyresampled($dst, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);
			
			imagejpeg($dst, $targetFile);
			
			imagedestroy($dst);
		} else {
			imagejpeg($src, $targetFile);
		}
		
		imagedestroy($src);
	}
}
