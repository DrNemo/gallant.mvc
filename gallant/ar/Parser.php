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

	function modelSelf(){
		$result = array();
		foreach ($this->source as $num_line => $line) {
			foreach ($line as $pref => $data){
				if($pref == 'self'){
					$result = $data;
				}else{
					$new_pref = substr($pref, 6);
					$this->source[$num_line][$new_pref] = $data;
				}
				unset($this->source[$num_line][$pref]);
			}
		}
		return $result;
	}

	function export($key_relation, $ids){
		$result = array();
		if(!is_array($ids)){
			$ids = array($ids);
		}
		foreach ($this->source as $num_line => $line){
			$new_id = array();
			foreach ($ids as $id) {
				$new_id[] = $line[$key_relation][$id];
			}
			$new_id = implode('-', $new_id);
			foreach ($line as $pref => $data){
				if(substr($pref, 0, strlen($key_relation)) == $key_relation){
					$post_pref = substr($pref, strlen($key_relation) + 2);
					$new_pref = ($post_pref) ? 'self::' . $post_pref : 'self';
					$result[$new_id][$num_line][$new_pref] = $data;
				}
			}
		}
		$f = function($val){
			return new Parser($val);
		};
		return array_map($f, array_values($result));
	}
}