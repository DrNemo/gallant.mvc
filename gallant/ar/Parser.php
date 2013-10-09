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
	private $source = false;
	private $data = false;
	private $po_source = false;
	private $children = array();
	function __construct($data){
		$this->source = $data;
	}

	function getParse($rule){
		$rule['self'] = Builder::MODEL_AS;

		if(!$this->source) return;
		$data = $this->source;

		$parse_data = array();
		foreach ($data as $line => $value) {
			$value = array_filter($value);
			foreach($value as $db_key => $db_val){
				foreach ($rule as $model => $pref) {
					if(substr($db_key, 0, strlen($pref)) == $pref){
						$parse_data[$line][$model][substr($db_key, strlen($pref) + 1)] = $db_val;
					}
				}				
			}
		}

		if(!$parse_data) return;

		$reparse_data = array();
		$pre_self = array();
		unset($rule['self']);
		foreach ($parse_data as $line) {
			if(array_diff_assoc($line['self'], $pre_self)){
				if($_data['self']){
					$reparse_data[] = new Parser($_data);					
					$_data = array();
				}
				$_data['self'] = $line['self'];
				$pre_self = $line['self'];
			}

			foreach ($rule as $module => $pref) {
				if($line[$module]){
					$_data[$module][] = $line[$module];
				}
			}
		}
		if($_data){
			$reparse_data[] = new Parser($_data);
		}

		return $reparse_data;
	}

	function getData($model){
		if('self' == $model){
			if($this->source['self']){
				$data = $this->source['self'];
				unset($this->source['self']);
				return $data;
			}
		}else{
			
			if($this->source[$model]){
				$return = array();
				$data_model = $this->source[$model];
				unset($this->source[$model]);
				while($self = array_shift($data_model)) {
					$data = $this->source;
					$data['self'] = $self;
					$return[] = new Parser($data);
				}
				return $return;
			}
		}
	}

}