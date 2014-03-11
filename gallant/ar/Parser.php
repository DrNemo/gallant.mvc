<?
/**
* Gallant\Ar\Parser
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Ar;

class Parser{
	protected $source = array();

	function __construct($source){
		$this->source = $source;
	}

	function modelSelf($model){
		$this->source = array_filter($this->source, 'sizeof');
		if(!$this->source) return array();
		$result = array();
		foreach ($this->source as &$line) {
			if($line[$model]){
				$result = $line[$model];
				unset($line[$model]);
				break;
			}
		}

		return $result;
	}

	function export($pref, $model){
		$this->source = array_filter($this->source, 'sizeof');
		if(!$this->source) return false;
		
		$ext = false;
		foreach ($this->source as &$line){
			if($line[$pref]){
				$line[$model] = $line[$pref];
				unset($line[$pref]);
				$ext = true;
				break;
			}
		}

		if($ext){
			return new Parser($this->source);
		}
		return false;
	}
}