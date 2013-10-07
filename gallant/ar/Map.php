<?
/**
* Map
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/
namespace Gallant\Ar;

class Map{
	private $source = false;
	private $pref = false;
	private $data = false;
	private $po_source = false;
	private $children = array();
	function __construct($data/*, $prefs*/){
		$this->source = $data;
		$this->pref = $prefs;
	}

	function getParse($as, $pks){

		if(!is_array($pks)){
			$ar_pk = array($pks);
		}else{
			$ar_pk = $pks;
		}		

		$sources = $this->source;

		$pre_key = '';
		$pre_val = '';
		$obj_data = array();

		if($sources){
			foreach($sources as $line){
				$line = array_filter($line);

				$new_key = $new_val = '';
				foreach($ar_pk as $_pk){
					$new_key .= 'key_'.$_pk . '_key';
					$new_val .= 'val_'.$line[$as.'_'.$_pk] . '_val';
				}

				if($pre_key != $new_key || $pre_val != $new_val){
					$pre_key = $new_key;
					$pre_val = $new_val;

					if($data) $obj_data[] = new Map($data, $this->pref);

					$data = array();
				}
				
				$data[] = $line;
			}
			if($data) $obj_data[] = new Map($data, $this->pref);
		}
		
		if(!$obj_data) return false;

		return $obj_data;
	}

	function getData($as){
		if(!sizeof($this->source)) return false;

		if($this->data){
			return $this->data;
		}

		$sources = $this->source;

		foreach ($sources as $num => $source) {
			foreach ($source as $db_key => $val) {
				if(substr($db_key, 0, strlen($as)) == $as){
					$this->data[substr($db_key, strlen($as) + 1)] = $val;
					unset($sources[$num][$db_key]);
					$this->po_source = $sources;
				}
			}
		}

		return $this->data;
	}

	function isSource(){
		return sizeof($this->po_source);
	}

	function getSource(){
		return $this->po_source;
	}

}