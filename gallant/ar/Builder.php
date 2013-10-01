<?
/**
* G
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/
namespace Gallant\Ar;

class Builder{

	protected $data = false;
	protected $data_update = array();
	protected $parent_models = array();

	private $_relation_pref = false;

	const MODEL_AS = 'mod1_t0';

	function __construct($data = false, $as = false){
		$this->sqlQuery();

		if($data && $as){
			$structure = $this->structure();
			$db_structure = $this->db_structure;
			$relations = $this->relations();

			$f_data = function($val, $key, $dop){
				if(substr($key, 0, strlen($dop['as'])) == $dop['as']){
					$dop['data'][substr($key, strlen($dop['as']) + 1)] = $val;
				}
			};
			while($line = array_shift($data)){

				if(!$this->data){
					$data_model = array();
					array_walk($line, $f_data, array('as' => $as, 'data' => &$data_model));
					$this->data = $data_model;					
				}

				if($relations && $as == self::MODEL_AS){
					foreach ($relations as $rel_key => $rel_desc){
						$rel_model = $rel_desc['model'];
						if(!$this->parent_models[$rel_key]){
							$this->parent_models[$rel_key] = new Mediator;
						}
						$model = new $rel_model(array($line), $this->_relation_pref[$rel_key]);

						$this->parent_models[$rel_key]->push($model);
					}
				}
			}
		}		
	}

	/* return model name */
	function className(){
		return get_called_class();
	}

	function publicMethod(){
		return Register::getStructure(self::className());
	}

	/* sql query generate */
	private function sqlQuery(){
		$model = self::className();

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
			$iter = 1;
			foreach ($relations as $key => $relation) {
				
				$rel_model = $relation['model'];
				$relation_type = $relation['relation'];
				$my_colon = $relation['my_column'];
				$his_colon = $relation['his_column'];
				if(is_array($his_colon)){
					$add_colons = array_slice($his_colon, 1);
					$his_colon = $his_colon[0];
				}

				$rel_as = 'mod1_t'.$iter;

				$rel_pk = $rel_model::primaryPk();


				$rel_colon = $rel_model::structure();

				array_walk($rel_colon, $f);					

				$rel_colon = array_values($rel_colon);

				$demo_query->columns($rel_colon, $rel_as, $rel_as.'_');

				$rel_concet[$key] = $rel_as;

				// ONE_TO_BUNCH - для связки использует доп таблицу
				if($relation_type == 'ONE_TO_BUNCH'){
					if(is_array($pk)){
						$pk = $pk[0];
					}

					$dop_table = $my_colon[0];

					$dop_pole = $my_colon[1];

					$iter++;

					$dop_as = 'mod1_t'.$iter;

					$demo_query->join(array($dop_table, $dop_as), "$dop_as.$dop_pole = $as.$pk");

					$r_pk = (is_array($rel_pk)) ? $rel_pk[0] : $rel_pk;
					$demo_query->join(array($rel_model::table(), $rel_as), "$dop_as.$his_colon = $rel_as.$r_pk");

					$demo_query->columns(array($dop_pole, $his_colon), $dop_as, $rel_as.'_');

					if(sizeof($my_colon) > 2){
						$demo_query->columns(array_splice($my_colon, 2), $dop_as, $rel_as.'_');
					}

					if($add_colons){
						$demo_query->columns($add_colons, $dop_as, $rel_as.'_');
					}

				// ONE_TO_ONE - один к одному
				}else if($relation_type == 'ONE_TO_ONE'){
					$demo_query->table($rel_model::table(), $rel_as)->where("$as.$my_colon == $rel_as.$his_colon");
				}
				
				$iter++;
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

		//Register::setStructure($model, $colon);
		//Register::setQuery($model, $demo_query);
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
			if(sizeof($gpk) > 1){
				if(!is_array($gpk[0])){
					$val = $gpk;
				}else{
					$val = $gpk[0];
				}
				$inp = implode(',', array_fill(0, sizeof($val), '?'));
				$sql .= " IN ($inp)";
				
			}else{
				$sql .= " = ?";
				//$attr[] = $gpk;
			}
			$attr = array_values($gpk);
		}
		
		$pre_query->where($sql)->attr($attr);

		if($results = $pre_query->select()){
			$data_return = self::parseResult($results);
			return $data_return;
		}else return false;		
	}

	static function fetch(\Gallant\DB\DBQuery $query){
		$as = self::MODEL_AS;
		$model = self::className();
		$pre_model = new $model;
		$pre_query = Register::getQuery($model);
		$pre_query->merge($query);
		
		if($results = $pre_query->select()){
			$data_return = self::parseResult($results);
			return $data_return;
		}else return false;	
	}

	function save(){
		$model = self::className();
		$pre_model = new $model;
		$pre_query = $pre_model->getQuery();		

		$f = function($val, $key, $dop){
			if(in_array($key, $dop['structure'])){
				$dop['update'][$key] = $val;
			}
		};

		$data = array();
		array_walk($this->data_update, $f, array('structure' => $this->db_structure, 'update' => &$data));

		if($this->data){
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

	private function parseResult($results){
		$as = self::MODEL_AS;
		$model = self::className();
		$pk = $model::primaryPk();

		if(!is_array($pk)){
			$ar_pk = array($pk);
		}else{
			$ar_pk = $pk;
		}
		$pre_id = false;
		$obj_data = array();
		foreach($results as $result){
			$new_pre_id = false;				
			foreach($ar_pk as $_pk){
				$new_pre_id[] = $result[$as.'_'.$_pk];
			}
			if(!$pre_id){
				$pre_id = $new_pre_id;
			}

			if($pre_id != $new_pre_id){
				$pre_id = $new_pre_id;
				$obj_data[] = $data;
				$data = array();
			}
			$data[] = $result;
		}
		$obj_data[] = $data;

		$result = new Mediator;	
		foreach($obj_data as $data){
			$result->push(new $model($data, $as));
		}
		return $result;
	}
}