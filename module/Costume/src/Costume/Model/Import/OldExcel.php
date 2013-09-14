<?php
namespace Costume\Model\Import;

class OldExcel
{

	public function readCsvFile($file, $logFile, $errorFile)
	{
		$data = array();
		
		fwrite($logFile, "----- Debut de lecture du fichier CSV $file ---------------\n");
		fwrite($errorFile, "----- Debut de lecture du fichier CSV $file ---------------\n");
		
		$handle = fopen($file, "r");
		if ($handle) {
			$row = 1;
			$line = fgets($handle);
			while ($line) {
				$line = utf8_encode(rtrim($line, "\r\n"));
				
				// Si la ligne commence par #####, fin de chargement du fichier
				if (preg_match('/^"?#####/u', $line)) {
					fwrite($logFile, "Ligne de fin de ficher (#$row) : $line\n");
					$line = false;
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
				$data[] = $tabLine;
				
				$line = fgets($handle);
				$row++;
			}
			fclose($handle);
		}
		fwrite($logFile, "----- Fin de lecture du fichier CSV $file ---------------\n\n");
		fwrite($errorFile, "----- Fin de lecture du fichier CSV $file ---------------\n\n");
		
		return $data;
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
