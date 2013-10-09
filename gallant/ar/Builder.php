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

	public $_relation_pref = false;

	const MODEL_AS = 'mod1_t0';

	const ONE_TO_ONE = 'relation::ONE_TO_ONE';
	const ONE_TO_MANY = 'relation::ONE_TO_MANY';
	const MANY_TABLE_BUNCH = 'relation::MANY_TABLE_BUNCH';
	const MANY_TO_MANY = 'relation::MANY_TO_MANY';

	function __construct($map = false, $as = false){
		$this->sqlQuery();

		$db_struct = Register::getStructure($this->className());
		//die();
		$arr_key = array_keys($db_struct);
		$arr_val = array();
		$arr_val = array_pad($arr_val, count($db_struct), false);
		$this->data = array_combine($arr_key, $arr_val);		
		
		
		if($map && $as){
			//p('__construct : '.$this->className(). ' | AS: '.$as, $map);
			
			$relations = $this->relations();

			// todo if($data is Map)
			$dt = $map->getData('self');
			if($dt){
				$this->data = array_merge($this->data, $dt);
				$this->_init = true;
			}else{
				return;
			}

			if($relations){

				foreach ($relations as $relation_key => $relation_data) {
					$rel_as = $this->_relation_pref[$relation_key];
					$rel_model_name = $relation_data['model'];
					$rel_type = $relation_data['relation'];

					$childs = $map->getData($relation_key);

					if($rel_type == self::ONE_TO_ONE){
						$this->parent_models[$relation_key] = new $rel_model_name($childs[0], $rel_as);
					}else if($rel_type == self::ONE_TO_MANY || $rel_type == self::MANY_TABLE_BUNCH){
						$mediator = new Iterator;
						if($childs){
							foreach ($childs as $child) {
								$new_child = new $rel_model_name($child, $rel_as);
								if($new_child->_init){
									$mediator->push($new_child);
								}
							}
						}
						$this->parent_models[$relation_key] = $mediator;
					}else{
						p('@todo: '.$rel_type);
						p($rel_map);
					}
				}
			}
		}		
	}

	/* return model name */
	function className(){
		return get_called_class();
	}

	static function tableName(){
		$model = self::className();
		$pre_query = new \Gallant\DB\DBQuery($model::provider());
		return $pre_query->tableName($model::table());
	}

	function publicMethod(){
		return Register::getStructure(self::className());
	}

	/* sql query generate */
	private function sqlQuery(){
		$model = self::className();

		if(Register::getQuery($model)){
			// @todo return;
		}

		$demo_query = new \Gallant\DB\DBQuery($model::provider());

		$colon = $model::structure();

		$pk = $model::primaryPk();

		$as = self::MODEL_AS;

		$f = function(&$val, $key){
			if($val['db_column']){
				$val = $val['db_column'];
			}else{
				$val = $key;
			}
		};

		array_walk($colon, $f);

		$demo_query->table($model::table(), $as)->columns(array_values($colon), $as, $as.'_');

		$relations = $model::relations();
		$rel_concet = array();

		if($relations){
			$iter = 0;
			foreach ($relations as $key => $relation) {
				$iter ++;

				$rel_model = $relation['model'];

				$rel_pk = $rel_model::primaryPk();
				
				
				$relation_type = $relation['relation'];
				$my_colon = isset($relation['my_column']) ? $relation['my_column'] : $pk;
				$his_colon = isset($relation['his_column']) ? $relation['his_column'] : $rel_pk;

				$rel_as = 'mod1_t'.$iter;

				$rel_colon = $rel_model::structure();

				array_walk($rel_colon, $f);					

				$rel_colon = array_values($rel_colon);

				$rel_concet[$key] = $rel_as;

				$rel_table = $rel_model::table();

				$demo_query->columns($rel_colon, $rel_as, $rel_as.'_');

				if($relation_type == self::ONE_TO_ONE){

					$demo_query->join(array($rel_table, $rel_as), "$as.$my_colon = $rel_as.$his_colon");

				}else if($relation_type == self::ONE_TO_MANY){

					$demo_query->join(array($rel_table, $rel_as), "$as.$my_colon = $rel_as.$his_colon");

				}else if($relation_type == self::MANY_TABLE_BUNCH){

					$bunch_table = $relation['table'];
					$demo_query->join(array($bunch_table, 'bunch'), "`bunch`.$my_colon = $as.$pk");
					$demo_query->join(array($rel_table, $rel_as), "`bunch`.$his_colon = $rel_as.$rel_pk");
					//p(array($rel_table, $rel_as), "$as.$my_colon = $rel_as.$his_colon");

				}else/* if($relation_type == 'ONE_TO_MANY')*/{
					die('@todo $relation_type in Builder.php:'.$relation_type);
				}
				
				
			}
		}

		Register::setStructure($model, $colon);
		Register::setQuery($model, $demo_query);
		$this->_relation_pref = $rel_concet;
	}

	static function fetchPk(){
		$as = self::MODEL_AS;
		$gpk = func_get_args();
		$model = self::className();
		$pre_model = new $model;

		$pre_query = Register::getQuery($model);
		
		$pk = $pre_model->primaryPk();

		if(is_array($pk)){
			$count = sizeof($pk);
			$sql = "";
			$attr = array();

			for($itr = 0; $itr < $count; $itr++){
				if($gpk[$itr]){
					if($sql) $sql .= ' AND ';

					$sql .= "$as.$pk[$itr]";

				
					if(is_array($gpk[$itr])){
						$inp = implode(',', array_fill(0, sizeof($gpk[$itr]), '?'));
						$sql .= " IN($inp) ";
						$attr = array_merge($attr, array_values($gpk[$itr]));
					}else{
						$sql .= " = ? ";
						$attr[] = $gpk[$itr];
					}
				}
			}
		}else{
			$sql = "$as.$pk ";
			if(is_array($gpk[0])){
				$val = $gpk[0];
				$inp = implode(',', array_fill(0, sizeof($val), '?'));
				$sql .= " IN ($inp)";
				$attr = array_values($gpk[0]);			
			}else{
				$sql .= " = ?";
				$attr = array_values($gpk);
			}
			
		}
		
		$pre_query->where($sql)->attr($attr);

		$replace = $pre_model->_relation_pref;
		$replace['self'] = $as;

		if($results = $pre_query->select($replace)){
			$data_return = self::parseResult($results, $replace);
			return $data_return;
		}else return false;		
	}

	static function fetch($query = false){ // \Gallant\DB\DBQuery 
		$as = self::MODEL_AS;
		$model = self::className();
		$pre_model = new $model;
		$pre_query = Register::getQuery($model);
		if($query){
			$pre_query->merge($query);
		}

		$replace = $pre_model->_relation_pref;
		$replace['self'] = $as;

		if($results = $pre_query->select($replace)){
			$data_return = self::parseResult($results, $pre_model->_relation_pref);
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
			$pks = $pre_model->primaryPk();
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

		$pk = $model::primaryPk();

		if(!is_array($pk)){
			$ar_pk = array($pk);
		}else{
			$ar_pk = $pk;
		}

		$map = new Parser($results);
		$maps = $map->getParse($rule_pref);
		
		$mediator = new Iterator;

		if(sizeof($maps) > 0){
			foreach ($maps as $map) {
				$mediator->push(new $model($map, self::MODEL_AS));
			}
		}

		return $mediator;

	}
}