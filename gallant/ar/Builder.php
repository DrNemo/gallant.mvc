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
	
	public $_init = false;
	protected $data = array();
	protected $data_update = array();
	protected $parent_models = array();

	const MODEL_AS = 'mod1_t0';

	const ONE_TO_ONE = 'relation::ONE_TO_ONE';
	const ONE_TO_MANY = 'relation::ONE_TO_MANY';
	const MANY_TABLE_BUNCH = 'relation::MANY_TABLE_BUNCH';
	const MANY_TO_MANY = 'relation::MANY_TO_MANY';

	function __construct($map = false, $as = false, $repl = false){
		
		$model = self::className();

		$db_struct = $this->loadStructure();
		$arr_key = array_keys($db_struct);
		
		$arr_val = array_pad(array(), count($db_struct), false);
		$this->data = array_combine($arr_key, $arr_val);		
		
		if($map && $as){
			//p('__construct : '.$this->className(). ' | AS: '.$as.' | $repl:|'.$repl, $map);
			
			$relations = $this->relations();

			$dt = $map->getData('self');
			if($dt){
				$this->data = array_merge($this->data, $dt);
				$this->_init = true;
			}else{
				return;
			}

			if($relations){
				if(!$repl){
					$prefs = Register::getReplace($this->className());
					$repl = $this->className();
				}else{
					$prefs = Register::getReplace($repl);
				}
				foreach ($relations as $relation_key => $relation_data) {
					$rel_as = $prefs[$model.':'.$relation_key];
					$rel_model_name = $relation_data['model'];
					$rel_type = $relation_data['relation'];

					$childs = $map->getData($model.':'.$relation_key);

					if($rel_type == self::ONE_TO_ONE){
						$pre_model = new $rel_model_name($childs[0], $rel_as, $repl);
						if($pre_model->_init){
							$this->parent_models[$relation_key] = $pre_model;
						}
					}else if($rel_type == self::ONE_TO_MANY || $rel_type == self::MANY_TABLE_BUNCH){
						$iters = array();
						if($childs){
							foreach ($childs as $child) {
								$new_child = new $rel_model_name($child, $rel_as, $repl);
								if($new_child->_init){
									$iters[] = $new_child;
								}
							}
						}
						$this->parent_models[$relation_key] = new Iterator($iters);
					}else{
						p('@todo: '.$rel_type);
						p($rel_map);
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

	static function tableName(){
		$model = self::className();
		$pre_query = new \Gallant\DB\DBQuery($model::provider());
		return $pre_query->tableName($model::table());
	}

	static function loadStructure(){
		$model = self::className();
		if(!$structure = Register::getStructure($model)){
			$structure = $model::structure();
			$my_pk = $model::primaryKey();

			$f = function(&$val, $key){
				if(isset($val['db_column'])){
					$val = $val['db_column'];
				}else{
					$val = $key;
				}
			};
			array_walk($structure, $f);
			Register::setStructure($model, $structure);
		}
		return $structure;
	}

	static function searchRelation(&$query, $my_as, &$iter = 0){
		$model = self::className();

		$structure = self::loadStructure();

		$query->columns(array_values($structure), $my_as, $my_as.'_');
		if($my_as == self::MODEL_AS){
			$query->table($model::table(), $my_as);
		}
		
		$my_pk = $model::primaryKey();
		$relations = $model::relations();
		if($relations){
			foreach ($relations as $relation_key => $relation) {
				if($my_as == self::MODEL_AS || @$relation['load']){
					++ $iter;
					$rel_model = $relation['model'];
					$rel_as = 'mod1_t'.$iter;					

					$rel_pk = $rel_model::primaryKey();

					$relation_type = $relation['relation'];
					$my_colon = isset($relation['my_column']) ? $relation['my_column'] : $my_pk;
					$his_colon = isset($relation['his_column']) ? $relation['his_column'] : $rel_pk;
					
					$rel_table = $rel_model::table();				

					if($relation_type == self::ONE_TO_ONE){

						$query->join(array($rel_table, $rel_as), "$my_as.$my_colon = $rel_as.$his_colon");

					}else if($relation_type == self::ONE_TO_MANY){

						$query->join(array($rel_table, $rel_as), "$my_as.$my_colon = $rel_as.$his_colon");

					}else if($relation_type == self::MANY_TABLE_BUNCH){

						$bunch_table = $relation['table'];
						$query->join(array($bunch_table, 'bunch'.$iter), "`bunch$iter`.$my_colon = $my_as.$my_pk");
						$query->join(array($rel_table, $rel_as), "`bunch$iter`.$his_colon = $rel_as.$rel_pk");

					}else{
						die('@todo $relation_type in Builder.php:'.$relation_type);				
					}

					
					if($new_pref = $rel_model::searchRelation($query, $rel_as, $iter)){
						$rel_concet[$model.':'.$relation_key] = $new_pref['self'];
						unset($new_pref['self']);
						$rel_concet = array_merge($rel_concet, $new_pref);
					}
					
				}
				
			}
		}

		$rel_concet['self'] = $my_as;
		return $rel_concet;
	}

	static function fetchPk(){
		$param_pk = func_get_args();
		$model = self::className();
		$as = self::MODEL_AS;
		$my_pk = $model::primaryKey();

		if(!$query = Register::getQuery($model) || $replace = Register::getReplace($model)){
			$query = new arQuery($model::provider());
			$replace = $model::searchRelation($query, self::MODEL_AS);

			Register::setQuery($model, $query);
			Register::setReplace($model, $replace);
		}

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

		$results = $query->select($replace);
		if($results){			
			$data_return = self::parseResult($results, $replace);			
			return $data_return;
		}else return false;
		
	}

	static function fetch($query = false){ // \Gallant\DB\DBQuery 
		//die('fetch');
		$as = self::MODEL_AS;
		$model = self::className();

		if(!$pre_query = Register::getQuery($model) || $replace = Register::getReplace($model)){
			$pre_query = new arQuery($model::provider());
			$replace = $model::searchRelation($pre_query, self::MODEL_AS);

			Register::setQuery($model, $query);
			Register::setReplace($model, $replace);
		}
		if($query){
			$pre_query->merge($query);
		}

		if($results = $pre_query->select($replace)){
			$data_return = self::parseResult($results, $replace);
			return $data_return;
		}else return false;	
	}

	function save(){
		
		$model = self::className();
		$pre_model = new $model;
		$pre_query = Register::getQuery($model);		

		$f = function($val, $key, $dop){
			if(in_array($key, $dop['structure'])){
				$dop['update'][$key] = $val;
			}
		};

		$data = array();
		array_walk($this->data_update, $f, array('structure' => Register::getStructure($model), 'update' => &$data));

		if($this->_init){
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

		
	}

	private static function parseResult($results, $rule_pref){
		$model = self::className();

		$pk = $model::primaryKey();

		if(!is_array($pk)){
			$ar_pk = array($pk);
		}else{
			$ar_pk = $pk;
		}

		$map = new Parser($results);

		$maps = $map->getParse($rule_pref);
		
		$data = array();
		if(sizeof($maps) > 0){
			foreach ($maps as $map) {
				$data[] = new $model($map, self::MODEL_AS);
			}
		}

		return new Iterator($data);

	}
}