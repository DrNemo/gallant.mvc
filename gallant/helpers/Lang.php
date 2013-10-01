<?
/**
* G
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Helpers;
use \G as G;

class Lang{
	private $lang = false;
	private $folder = false;
	function __construct($lang){
		$this->lang = $lang;
		$this->setFolder(G::getPath('lang'));
	}

	function getLang(){
		return $this->lang;
	}

	function setLang($lang){
		$this->lang = $lang;
	}

	function setFolder($folder){
		$this->folder = $folder;
	}

	function getWord($key, $lang = false){
		return 'LANG:'.$this->lang.':'.$key;
	}
}