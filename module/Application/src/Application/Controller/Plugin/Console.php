<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\Console\Console as ZendConsole;
use Application\Toolbox\String as StringTools;

class Console extends AbstractPlugin
{

	/**
	 * Objet de session
	 *
	 * @var Zend\Console\Adapter\AdapterInterface
	 */
	protected $console;

	public function __construct(ConsoleAdapter $console = null)
	{
		if ($console && $console instanceof ConsoleAdapter) {
			$this->console = $console;
		} else {
			$this->console = ZendConsole::getInstance();
		}
	}

	/**
	 * Permet de rediriger l'appel des méthodes de l'objet Console Zend.
	 *
	 * Ainsi le plugin console() est également un Helper d'accès aux méthodes standard de la Console Zend (write, writeLine, ...)
	 * 
	 * @param unknown $name
	 * @param unknown $arguments
	 * 
	 * @throws \Exception
	 */
	public function __call($name, $arguments)
	{
		if (method_exists($this->console, $name)) {
			call_user_func_array(array(
				$this->console,
				$name
			), $arguments);
		} else {
			throw new \Exception("Method '$name' is not a valid methode for object " . get_class($this->console));
		}
	}

	/**
	 * Affiche un tableau en texte sur la console
	 *
	 * Affiche un tableau de la forme :
	 *
	 * Col1 | Col2 | Col3
	 * -----------------------
	 * x | yz | p
	 * ab | cdefghi | xzf
	 *
	 * A partir d'un tableau à 2 dimension de données, et éventuellement d'un tableau d'en-tête.
	 *
	 * Si aucun header n'est fournit, il ne sera pas affiché.
	 *
	 * S'il n'y a aucune données, rien est affiché sauf s'il y a un header fourni. Il est alors affiché seul.
	 *
	 * La largeur de chaque colonne est calculée automatiquement en fonction de la taille maximal des valeurs ou du header.
	 * Si la largeur du tableau est plus grand que la largeur de la console, le tableau sera tronqué à droite.
	 *
	 * @param array[] $data Tableau à 2 dimension représentant les données à afficher (sous forme de chaine).
	 * @param string[] $headers Liste de chaine représentant les en-tête à afficher (facultatif)
	 */
	public function writeTable($data, $headers = array())
	{
		// Si pas de données et pas d'en-tête, alors rien à afficher
		if (!count($data) && !count($headers)) {
			return;
		}
		
		// Combien de colonnes ? On compte en priorité les éléments du header s'il y en a,
		// sinon on compte les éléments de la première ligne de données (s'il y en a).
		$nbCols = 0;
		if (count($headers)) {
			$nbCols = count($headers);
		} else 
			if (count($data) && (is_array($data[0]))) {
				$nbCols = count($data[0]);
			}
		
		// Si aucune colone, alors rien à afficher
		if (!$nbCols) {
			return;
		}
		
		// Calcul de la longueur du contenu de chaque colonne
		$colWidth = array_fill(0, $nbCols, 0);
		if (count($data)) {
			foreach ($data as $line) {
				$colIdx = 0;
				foreach ($line as $cell) {
					if (mb_strlen($cell) > $colWidth[$colIdx]) {
						$colWidth[$colIdx] = strlen($cell);
					}
					$colIdx++;
				}
			}
		}
		// S'il y un header, on l'inclu ausis dans le calcul de la largeur des colones
		if (count($headers)) {
			$colIdx = 0;
			foreach ($headers as $cell) {
				if (mb_strlen($cell) > $colWidth[$colIdx]) {
					$colWidth[$colIdx] = strlen($cell);
				}
				$colIdx++;
			}
		}
		// On ajoute 2 à chaque colonne (espace de padding gauche et droite)
		array_walk($colWidth, function (&$value)
		{
			$value += 2;
		});
		
		$tableWidth = array_sum($colWidth) + $nbCols - 1;
		
		// S'il y a un header, on l'affiche
		if (count($headers)) {
			$lineTab = array();
			for ($colIdx = 0; $colIdx < $nbCols; $colIdx++) {
				$lineTab[] = StringTools::mb_str_pad(' ' . $headers[$colIdx] . ' ', $colWidth[$colIdx]);
			}
			$this->console->writeLine(join('|', $lineTab));
			$this->console->writeLine(str_pad('', $tableWidth, '-'));
		}
		
		// Et pour finir on affiche les données s'il y en @author olivier
		if (count($data)) {
			foreach ($data as $line) {
				$lineTab = array();
				for ($colIdx = 0; $colIdx < $nbCols; $colIdx++) {
					$lineTab[] = StringTools::mb_str_pad(' ' . $line[$colIdx] . ' ', $colWidth[$colIdx]);
				}
				$this->console->writeLine(join('|', $lineTab));
			}
		}
	}
}
