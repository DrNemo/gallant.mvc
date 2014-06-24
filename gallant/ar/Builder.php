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

use \G;
use \Gallant\DB\SqlQuery;
use \Gallant\Exceptions\ArException;

class Builder{
	
	public $_init_ = false;
	protected $data = array();
	protected $data_update = array();
	protected $children_models = array();

	const ONE_TO_ONE = 'relation::ONE_TO_ONE';
	const ONE_TO_MANY = 'relation::ONE_TO_MANY';
	const MANY_TABLE_BUNCH = 'relation::MANY_TABLE_BUNCH';

	const CONCET_MODEL = '::';

	/**
	* 
	*/
	final function __construct($data = false){
		$model = $this->className();
		$struct_key = array_keys($this->structure());
		$this->data = array_combine($struct_key, array_pad(array(), sizeof($struct_key), false));
		
		if($data instanceof Parser){
			$this->data = array_merge($this->data, $data->modelSelf($model));
			$this->_init_ = true;

			$rels = $this->relations();
			foreach ($rels as $rel_key => $rel_opc){
				if(isset($rel_opc['load']) && $rel_opc['load'] === false){
					$this->children_models[$rel_key] = null;
				}else{
					$rel_model = ($rel_opc['model'][0] == '\\') ? substr($rel_opc['model'], 1) : $rel_opc['model'];

					$parse_pref = $model . self::CONCET_MODEL . $rel_key;

					if($rel_opc['relation'] == self::ONE_TO_ONE){
						$exp = $data->export($parse_pref, $rel_model);
						if($exp){
							$this->children_models[$rel_key] = new $rel_model($data);
						}else{
							$this->children_models[$rel_key] = false;
						}
					}else{
						$_data = array();

						$pks = $rel_model::primaryKey();
						if(!is_array($pks)){
							$pks = array($pks);
						}
						$pk = $pks[0];

						while($exp = $data->export($parse_pref, $rel_model)){
							$mod = new $rel_model($data);
							if(!$data_save[$mod->$pk]){
								$data_save[$mod->$pk] = true;
								$_data[] = $mod;
							}
						}
						$this->children_models[$rel_key] = new Iterator($_data);
					}
				}
			}
			$this->onLoad();
		}else if(is_array($data)){
			$this->setData($data);
		}
	}

	/**
	* className 
	* @return Class Model Name
	*/
	final static function className(){
		return get_called_class();
	}

	/**
	* serialize 
	* @return serialize object Model
	*/
	function serialize(){
		return serialize($this);
	}

	/**
	* private dbStructure
	* @return structure db table Model
	*/
	static private function dbStructure(){		
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

	/**
	*
	*
	*/
	static private function queryConstruct($criteria_or_pref, $iter_model = 0){		
		$model = self::className();
		$model_provider = $model::provider();

		$query = new SqlQuery($model_provider);
		$glob_query = new SqlQuery($model_provider);

		$pref = "m{$iter_model}_t";

		$iter_table = 0;
		$model_pref = $pref.$iter_table; 
		$iter_table ++;
		

		$structure = $model::dbStructure();

		$comby_key_pref = array();

		$func_pref = function($val) use (&$my_pref){
			return "$my_pref.$val AS `{$my_pref}_{$val}`";
		};		

		$parent_model = false;
		$criteria = false;
		if($criteria_or_pref instanceof Criteria){
			$parent_model = true;
			$criteria = $criteria_or_pref;
			$func_cow = function($val){
				return "`$val`";
			};

			$query->columns(array_map($func_cow, $structure))->table($model::table());

			// where local
			if($where = $criteria->get('where')){
				$query->where($where);
			}

			// limit local
			if(($limit = $criteria->get('limit')) !== false){
				if(!isset($limit[1])) $limit[1] = false;
				$query->limit($limit[0], $limit[1]);
			}

			// order local
			if(!($order = $criteria->get('order'))){
				$order = array();
				$struc = $model::structure();
				foreach ($struc as $key => $param){
					if(isset($param['order'])){
						if(isset($param['column'])){
							$k = $param['column'];
						}else{
							$k = $key;
						}
						$order[] = array($k, $param['order']);
					}
				}
			}
			if($order){
				$ord_func = function($val) use ($model_pref){
					return array("{$model_pref}.{$val[0]}", $val[1]);
				};			
				$query->order($order);
				$order = array_map($ord_func, $order);
				$glob_query->order($order);
			}

			// attr local
			if($attr = $criteria->get('attr')){
				$glob_query->attr($attr);
			}

			// where
			$my_pref = $model_pref;
			$columns = array_map($func_pref, $structure);
			$glob_query->columns($columns)->table($query, $model_pref);
			$comby_key_pref[$model] = $model_pref;
		}else{
			$model_pref = $criteria_or_pref;
		}


		$relations = $model::relations();
		if($relations && is_array($relations) && sizeof($relations)){
			
			foreach ($relations as $key => $relation){
				$load = false;
				if($parent_model && !isset($relation['load'])){
					$load = true;
				}
				if(isset($relation['load']) && $relation['load']){
					$load = true;
				}
				if($load){					
					$rel_model = $relation['model'];
					if(!class_exists($rel_model)){
						throw new ArException("not fount Model: $rel_model");
					}
					if($rel_model::provider() != $model_provider){
						throw new ArException("providers do not match");
					}					

					$rel_pref = $pref.$iter_table;
					$iter_table ++;

					$rel_col = $rel_model::dbStructure();

					$my_pref = $rel_pref;
					$rel_cols = array_map($func_pref, $rel_col);
					$glob_query->columns($rel_cols);

					if($criteria){
						$rel_orders = $criteria->get('orderRelation');
						if($rel_orders) foreach ($rel_orders as $rel_order) {
							if($rel_order[0] == $key){
								$glob_query->order("$rel_pref.`$rel_order[1]`", $rel_order[2]);
							}
						}
						$rel_wheres = $criteria->get('whereRelation');
						if($rel_wheres) foreach($rel_wheres as $rel_where_key => $rel_where){
							if($rel_where_key == $key){
								$glob_query->where($rel_pref.'.'.$rel_where);
							}
						}
					}

					if($relation['relation'] == self::MANY_TABLE_BUNCH){
						$my_column = isset($relation['my_column']) ? $relation['my_column'] : $model::primaryKey();
						$his_column = isset($relation['his_column']) ? $relation['his_column'] : $rel_model::primaryKey();

						$m_pk = $model::primaryKey();
						$r_pk = $rel_model::primaryKey();
						$bunch_pref = uniqid();

						$glob_query->join(array($relation['table'], $bunch_pref), "`$model_pref`.`$m_pk` = `$bunch_pref`.`$my_column`");
						$glob_query->join(array($rel_model::table(), $rel_pref), "`$rel_pref`.`$r_pk` = `$bunch_pref`.`$his_column`");
					}else{
						$my_column = isset($relation['my_column']) ? $relation['my_column'] : $model::primaryKey();
						$his_column = isset($relation['his_column']) ? $relation['his_column'] : $rel_model::primaryKey();

						$glob_query->join(array($rel_model::table(), $rel_pref), "`$model_pref`.`$my_column` = `$rel_pref`.`$his_column`");
					}

					$comby_key_pref[$model.'::'.$key] = $rel_pref;
					$iter_model += 1;
					list($rel_query, $rel_query_pref) = $rel_model::queryConstruct($rel_pref, $iter_model);
					$glob_query = $glob_query->merge($rel_query);
					$comby_key_pref = array_merge($comby_key_pref, $rel_query_pref);					
				}
			}
		}
		
		return array($glob_query, $comby_key_pref);		
	}

	static function parser($source, $prefs){
		$model = self::className();
		$pks = $model::primaryKey();
		if(!is_array($pks)){
			$pks = array($pks);
		}

		$pre_pars = array();

		if(!$source) return $pre_pars;

		$data = array();

		foreach ($source as $line) {
			$line = array_filter($line);
			if($line){
				$line_data = array();
				foreach($line as $key => $value){
					foreach ($prefs as $rel_name => $prefix) {
						if(substr($key, 0, strlen($prefix)) == $prefix){
							$line_data[$rel_name][substr($key, strlen($prefix) + 1)] = $value;
						}
					}
				}

				$new_id = array();
				foreach ($pks as $pk) {
					$new_id[] = $line_data[$model][$pk];
				}
				$new_id = implode('_', $new_id);

				$data[$new_id][] = $line_data;
			}
			
		}
		$func_class = function($val) use ($model){
			return new $model(new Parser($val));
		};

		return array_map($func_class, $data);
	}
	/**
	* fetch
	* @param \Gallant\Ar\Criteria - подготовленный с помощью Criteria запрос к БД
	* @return Iterator - result model
	*/
	static function fetch($criteria = false){
		$model = self::className();

		if(!$criteria){
			$criteria = $model::criteria();
		}
		if(!($criteria instanceof Criteria)){
			throw new ArException('fetch argument : class Criteria (use MyModel::criteria())');
		}

		$query = self::queryConstruct($criteria);

		$res = $query[0]->select();

		$data = array();
		$data = self::parser($res, $query[1]);

		return new Iterator($data);	
	}

	/**
	* fetchPk
	* @param mixid - primaryKey model
	* Model::fetchPk(1)
	* Model::fetchPk( array(1, 2, 3, 4 ,...) )
	* @return Iterator - result model
	*/
	static function fetchPk($pk){
		if(!$pk){
			throw new ArException("fetchPk: $pk");
		}
		$model = self::className();
		$result = array();

		if(!is_array($pk)){
			$pk = array($pk);
		}

		$primaryKey = $model::primaryKey();
		if(!is_array($primaryKey)){
			$primaryKey = array($primaryKey);
		}

		$func_pref = function($val) use ($primaryKey){
			return ":{$primaryKey[0]}_{$val}";
		};

		$poles = array_map($func_pref, $pk);
		$attr = array_combine($poles, $pk);

		$criteria = $model::criteria()->where("{$primaryKey[0]} IN (" . implode(',', $poles). ")")->attr($attr);

		$query = self::queryConstruct($criteria);

		$res = $query[0]->select();

		$result = self::parser($res, $query[1]);

		return new Iterator($result);
	}


	/**
	* @todo save - save data Model in db
	* @todo insert model / update model 
	* @param bool (@todo true - сохранит все связанные модели, false - сохранение только данные этой модели)
	* @return mixed = если insert, то primaryKey Model. если update, то boolen: 
	* 	int - успешно, количество затронутых рядов. Внимание, быть 0 если модель не изменялась
	*	false - ошибка (если данные модели не изменились запрос отправляться не будет, вернет false)
	*/
	function save($save_as = false){
		$model = self::className();
		$this->onBeforeSave();

		$query = G::dbQuery($model::provider())->table($model::table());
		$data = array_intersect_key($this->data_update, $this->data);
		if(!$data) return false;

		if($this->_init_){
			$pks = $model::primaryKey();
			if(!is_array($pks)) $pks = array($pks);

			$where = '';
			foreach($pks as $pk){
				$attr[":gallant_update_pk_$pk"] = $this->data[$pk];
				unset($data[$pk]);
				if($where) $where .= "OR";
				$where .= " $pk = :gallant_update_pk_$pk ";
			}			

			$data = array_merge($data, $attr);
			$res = $query->where($where)->attr($data)->update();
			if($res){
				$this->onAfterSave();
				/**
				* @todo in log
				*/
				return $res;
			}else{
				/**
				* @todo in log
				*/
				p('save update false');
				return false;
			}
			
			
		}else{
			// insert
			$pks = $model::primaryKey();
			if(!is_array($pks)){
				$pks = array($pks);
			}
			$new_pk = $query->attr($data)->insert();
			
			if($new_pk){
				if(!is_array($new_pk)){
					$_new_pk = array($new_pk);
				}
				$this->data = array_merge($this->data, $data);
				$this->data_update = array();

				foreach ($pks as $pk){
					$this->data[$pk] = array_shift($_new_pk);
				}

				$this->_init_ = true;
				$this->onAfterSave();
				/**
				* @todo in log
				*/
				return $new_pk;
			}else{
				/**
				* @todo in log
				*/
				p('save insert false');
				return false;
			}			
		}
	}


	/**
	* @todo delete
	* @todo delete data Model in db
	* @param bool (@todo true - удалит все связанные модели, false - удалит только данные этой модели)
	* @return bool
	* 	true - успешно
	*	false - ошибка
	*/
	function delete($delete_as = false){
		$model = self::className();

		$query = G::dbQuery($model::provider())->table($model::table());

		$pks = $model::primaryKey();
		if(!is_array($pks)) $pks = array($pks);

		$where = "";
		$attr = array();
		foreach ($pks as $pk) {
			if(strlen($where)) $where .= " AND ";
			$where .= "$pk = :$pk";
			$attr[":$pk"] = $this->$pk;
		}
		$result = $query->where($where)->attr($attr)->delete();

		$this->onDelete();
		return $result;
	}

	/**
	* related возвращяет связаные модели
	*
	* @param string 'model_key'
	* @return class Gallant\Ar\Iterator
	*/
	public function related($rel){
		$model = self::className();
		$rels = $this->relations();

		if(!($relation = $rels[$rel])){
			throw new ArException("not fount relation: $rel");
		}
		if(!is_null($this->children_models[$rel])){
			return $this->children_models[$rel];
		}else{
			$relation = $rels[$rel];

			$rel_model = $relation['model'];

			if(!class_exists($rel_model)){
				throw new ArException("not fount Model: $model");
			}

			$my_column = isset($relation['my_column']) ? $relation['my_column'] : $model::primaryKey();
			$his_column = isset($relation['his_column']) ? $relation['his_column'] : $rel_model::primaryKey();

			$rel_id = $this->$my_column;
			if(!$rel_id) return new Iterator;

			$criteria = $rel_model::criteria()->where("$his_column = :val")->attr(array(':val' => $rel_id));
			$data = $rel_model::fetch($criteria);

			/**
			* @todo: MANY_TABLE_BUNCH
			*/
			if($relation['relation'] == self::MANY_TABLE_BUNCH){
				throw new ArException('@todo: load related model');
			}

			if($relation['relation'] == self::ONE_TO_ONE){
				$data = $data->first();
			}

			$this->children_models[$rel] = $data;
			return $this->children_models[$rel];
		}
	}

	static function count(){
		$model = self::className();
		$colons = $model::primaryKey();
		if(!is_array($colons)){
			$colons = array($colons);
		}
		$colon = array_shift($colons);
		$row = G::dbQuery($model::provider())->table($model::table())->columns(array("COUNT($colon) AS count_items"))->select();

		return ($row[0]['count_items']) ? $row[0]['count_items'] : 0;
	}
}