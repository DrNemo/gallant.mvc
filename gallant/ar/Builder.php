<?
/**
* Gallant\Ar\Builder
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/
namespace Gallant\Ar;

class Builder{
	
	public $_init_ = false;
	protected $data = array();
	protected $data_update = array();
	protected $parent_models = array();

	const MODEL_AS = 'mod0_t0';

	const ONE_TO_ONE = 'relation::ONE_TO_ONE';
	const ONE_TO_MANY = 'relation::ONE_TO_MANY';
	const MANY_TABLE_BUNCH = 'relation::MANY_TABLE_BUNCH';
	const MANY_TO_MANY = 'relation::MANY_TO_MANY';

	const CONCET_MODEL = '::';

	function __construct($parser = false){
		$model = self::className();

		$colons = $model::dbStructure();		
		$arr_key = array_keys($colons);

		$arr_val = array_pad(array(), count($colons), false);
		$this->data = array_combine($arr_key, $arr_val);

		if($parser){
			//p('__construct : '.$this->className(). ' | AS: '.$as.' | $repl:'.$repl.'|', $parser);

			$self = $parser->getSelf();
			$this->data = array_merge($this->data, $self);

			$this->_init_ = true;

			if($relations = $this->relations()){
				foreach ($relations as $relation_key => $relation_data) {
					$rel_type = $relation_data['relation'];
					$rel_model_name = $relation_data['model'];

					$rel_data = $parser->getRelation($relation_key);

					if($rel_data){
						if($rel_type == self::ONE_TO_ONE){
							$this->parent_models[$relation_key] = new $rel_model_name(new Parser($rel_data));
						}else{
							foreach ($rel_data as $_rel_data) {
								$_rel[] = new $rel_model_name(new Parser($_rel_data));
							}
							$this->parent_models[$relation_key] = new Iterator($_rel);
						}
					}
				}
			}
		}
	}

	// return model name 
	function className(){
		return get_called_class();
	}

	function serialize(){
		return serialize($this);
	}

	static function dbStructure(){		
		$model = self::className();

		if($r = Register::getStructure($model)){
			return $r;
		}

		$colons = $model::structure();
		$f = function(&$val, $key){
			if(isset($val['db'])){
				$val = $val['db'];
			}else{
				$val = $key;
			}
		};
		array_walk($colons, $f);

		Register::setStructure($model, $colons);
		return $colons;
	}

	static function buildSql($as, $num_model = 0){
		$table = 0;
		$model = self::className();

		if($as == self::MODEL_AS){
			$data = Register::get($model);
			if($data) return $data;
		}

		$pref = array();

		$pre_query = new arQuery($model::provider());

		if($as == self::MODEL_AS){
			$pref[$model.self::CONCET_MODEL.'self'] = $as;
			$pre_query->table($model::table(), $as);
			$table ++;
		}

		$pre_query->columns(array_values($model::dbStructure()), $as);
		
		if($relations = $model::relations()){
			foreach($relations as $rel_key => $relation){
				if($as == self::MODEL_AS || $relation['load']){
					

					$rel_as			= 'mod'.$num_model.'_t'.$table;
					$rel_model   	= $relation['model'];
					$rel_type    	= $relation['relation'];
					$rel_my_colon  	= isset($relation['my_column']) ? $relation['my_column'] : $model::primaryKey();
					$rel_his_colon 	= isset($relation['his_column']) ? $relation['his_column'] : $rel_model::primaryKey();

					$pref[$model.self::CONCET_MODEL.$rel_key] = $rel_as;

					$rel_query = $rel_model::buildSql($rel_as, $num_model + 1);

					if($rel_query){
						if($rel_type == self::MANY_TABLE_BUNCH){
							$bunch_table = $relation['table'];
							$bunch_as = 'bunch'.$table;
							$pre_query
								->join(array($bunch_table, $bunch_as), "`$bunch_as`.$rel_my_colon = $as.".$model::primaryKey())
								->join(array($rel_model::table(), $rel_as), "`$bunch_as`.$rel_his_colon = $rel_as.".$rel_model::primaryKey())
								->merge($rel_query[0]);
							$table ++;
						}else{
							$pre_query->join(array($rel_model::table(), $rel_as), "$as.$rel_my_colon = $rel_as.$rel_his_colon")->merge($rel_query[0]);
							$pref = array_merge($pref, $rel_query[1]);
						}	
					}
					$table ++;
				}
			}
		}

		if($as == self::MODEL_AS){
			Register::set($model, array($pre_query, $pref));
			return array($pre_query, $pref);
		}

		return array($pre_query, $pref);
	}

	static function preParser($source){
		$model = self::className();

		$pks = $model::primaryKey();
		if(!is_array($pks)){
			$pks = array($pks);
		}

		$len = strlen(self::MODEL_AS);
		$pre_pars = array();

		if(!$source) return $pre_pars;

		foreach ($source as $line) {
			$_new_self = array();
			foreach ($pks as $pk) {
				$_new_self[$pk] = $line[self::MODEL_AS . '_' . $pk];
			}
			$pre_pars[implode('_', $_new_self)][] = $line;			
		}	

		return array_values($pre_pars);
	}

	static function fetch($op_query = false){ // \Gallant\DB\DBQuery 
		$model = self::className();
		list($pre_query, $prefs) = self::buildSql(self::MODEL_AS);

		if($op_query){
			$pre_query->merge($op_query);
		}

		$data =  $pre_query->select($prefs);

		$result_parse = self::preParser($data);

		$result = array();
		foreach ($result_parse as $data) {
			$result[] = new $model(new Parser($model, $data, $prefs));
		}

		return new Iterator($result);		
	}

	static function fetchPk(){
		$model = self::className();
		$param_pk = func_get_args();
		$as = self::MODEL_AS;
		$my_pk = $model::primaryKey();		

		list($query, $prefs) = self::buildSql(self::MODEL_AS);

		if(is_array($param_pk[0])){
			$pks = $param_pk[0];
		}else if(sizeof($param_pk) > 1){
			$pks = $param_pk;
		}else{
			$pks = array($param_pk[0]);
		}

		if(sizeof($pks) > 1){
			$query->where("`$as`.`$my_pk` IN (".implode(',', array_fill(0, sizeof($pks), '?')).")")->attr($pks);
		}else{
			$query->where("`$as`.`$my_pk` = ?")->attr($pks);
		}

		$data =  $query->select($prefs);

		$result_parse = self::preParser($data);

		$result = array();
		foreach ($result_parse as $data) {
			$result[] = new $model(new Parser($model, $data, $prefs));
		}

		return new Iterator($result);
	}

	function save(){
		$model = self::className();

		list($query, $prefs) = self::buildSql(self::MODEL_AS);

		$data = array_merge($this->data, $this->data_update);

		$query->attr($data);

		if($this->_init_){
			// update

		}else{
			// insert

		}
		/*
		
		$pre_model = new $model;

		if(!($pre_query = Register::getQuery($model)) || !($replace = Register::getReplace($model))){
			$pre_query = new arQuery($model::provider());
			$replace = $model::searchRelation($pre_query, self::MODEL_AS);



			Register::setQuery($model, $pre_query);
			Register::setReplace($model, $replace);
		}

		$f = function($val, $key, $dop){
			if(in_array($key, $dop['structure'])){
				$dop['update'][$key] = $val;
			}
		};

		$data = array();
		array_walk($this->data_update, $f, array('structure' => Register::getStructure($model), 'update' => &$data));

		if($this->_init_){
			// update
			$pks = $pre_model->primaryKey();
			if(!is_array($pks)){
				$pks = array($pks);
			}
			$where = '';
			foreach($pks as $pk){
				$pre_query->where(" $pk = :gallant_update_pk_$pk ");
				$attr[":gallant_update_pk_$pk"] = $this->data[$pk];
			}
			$data = array_merge($data, $attr);
			
			$res = $pre_query->attr($data)->update();
			return $res;
		}else{
			// insert
			$id = $pre_query->attr($data)->insert();
			return $id;
		}
		*/
		
	}
}