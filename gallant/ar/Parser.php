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
	protected $data = array();
	protected $cursor = false;

	function __construct($model_or_data, $source = false, $prefs = false){
		if($source && $prefs){
			$this->model = $model_or_data;
			$this->prefs = $prefs;
			$this->source = $source;

			$this->data = $this->tree();
			$this->source = array();
		}else{
			$this->data = $model_or_data;
		}
	}

	protected function tree($model = false, $as = false){
		if(!$model){
			$model = $this->model;
			$pref = $this->prefs[$model.Builder::CONCET_MODEL.'self'];
		}else{
			$pref = $as;
		}

		$len = strlen($pref);
		$data = array();

		foreach ($this->source as $line_num => $line) {			
			foreach ($line as $db_key => $db_val){
				
				if(substr($db_key, 0, strlen($pref)) == $pref){
					$data['self'][substr($db_key, strlen($pref) + 1)] = $db_val;//.' | pref:'.$pref.' | model:'.$model;
					unset($this->source[$line_num][$db_key]);
				}
			}
			if($data['self']) break;
		}

		if($relations = $model::relations()){
			$data['relations'] = array();
			
			foreach($relations as $rel_key => $relation){
				if($pref == Builder::MODEL_AS || $relation['load']){

					$rel_as			= $this->prefs[$model.Builder::CONCET_MODEL.$rel_key];
					$rel_model   	= ($relation['model'][0] == '\\') ? substr($relation['model'], 1) : $relation['model'];
					$rel_type    	= $relation['relation'];					
					
					$data['relations'][$rel_key] = array();	

					if($rel_type == Builder::ONE_TO_ONE){
						if($tree = $this->tree($rel_model, $rel_as)){
							$data['relations'][$rel_key] = $tree;
						}
					}else{
						foreach ($this->source as $line_num => $line) {
							if($tree = $this->tree($rel_model, $rel_as)){
								$data['relations'][$rel_key][] = $tree;
							}								
						}
					}
				}

			}
			
		}
		return $data;
	}

	function getSelf(){
		if($this->data['self']){
			return array_filter($this->data['self']);
		}
		return false;
	}

	function getRelation($key){

		return $this->data['relations'][$key];
	}
}