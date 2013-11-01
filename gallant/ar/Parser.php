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
						$data['relations'][$rel_key] = $this->tree($rel_model, $rel_as);
					}else{
						foreach ($this->source as $line_num => $line) {
							$data['relations'][$rel_key][] = $this->tree($rel_model, $rel_as);
						}
					}
				}

			}
			
		}

		return $data;
	}

	function getSelf(){
		return $this->data['self'];
	}

	function getRelation($key){

		return $this->data['relations'][$key];
	}


	/* 
	function getSelf(){
		if($this->data['self']) return $this->data['self'];
		return array();
	}

	function getDataModel($model, $rel_type){

	}

	function getChilds($mod_pref, $rel_type){
		p('getChilds', $mod_pref, $rel_type);

		if($this->data['relation']){
			foreach ($this->data['relation'] as $num_line => $line){
				if(isset($line[$mod_pref])){
					$self[] = $line[$mod_pref];
				}
				
			}
		}

		return $self;
	}

	/*
	static protected $model_structure = array();

	static function setModelParser($name, $parser){
		self::$model_structure[$name] = $parser;
	}

	static function getModelParser($name){
		if(isset(self::$model_structure[$name])){
			return self::$model_structure[$name];
		}
		/*$pre_query = $name::build();
		if($pre_query){
			self::setModelParser($name, $parser)

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
				if(isset($_data['self'])){
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
		//p('$reparse_data', $reparse_data);

		return $reparse_data;
	}

	function getData($model, $type_rel = Builder::ONE_TO_ONE){
		if('self' == $model){
			if($this->source['self']){
				$data = $this->source['self'];
				unset($this->source['self']);
				return $data;
			}
		}else{
			//p($model, $type_rel);
			if(isset($this->source[$model])){
				/*if($type_rel == Builder::ONE_TO_ONE){
					$self = array_shift($this->source[$model]);
					$data = $this->source;
					$data['self'] = $self;
					$return[] = new Parser($data);
				}else{///
					$data_model = $this->source[$model];
					unset($this->source[$model]);
					while($self = array_shift($data_model)) {
						$data = $this->source;
						$data['self'] = $self;
						$return[] = new Parser($data);
					}
				//}
				//p($return, '--------');
				return $return;
			}
			/*
			if(isset($this->source[$model])){
				$return = array();
				if($one){
					$data = array_shift($this->source[$model]);
					$data['self'] = $self;
					$return = new Parser($data);
				}else{
					$data_model = $this->source[$model];
					unset($this->source[$model]);
					while($self = array_shift($data_model)) {
						$data = $this->source;
						$data['self'] = $self;
						$return[] = new Parser($data);
					}
					return $return;
				}
			}/
		}
	}*/



}