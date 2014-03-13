<?
/**
* Gallant\DB\DBProviderMysql
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\DB;

use \PDO;
use \Gallant\Exceptions\CoreException;

class DBProviderMysql{
	private $pdo = false;
	private $report = array();

	function __construct($config){
		if (!class_exists('\PDO')){
			throw new CoreException('Fatal Error: To work needs the support of PDO.');
		}
		try{
			if(!$this->pdo = new PDO("mysql:host=$config[host];dbname=$config[table]", $config['user'], $config['pass'])) die('Fatal Error: Error connect');
			$this->pdo->query("SET CHARACTER SET $config[character]");		
			$this->pdo->query("SET character_set_client = '$config[character]'");
			$this->pdo->query("SET character_set_connection = '$config[character]'");
			$this->pdo->query("SET character_set_results = '$config[character]'");
		}catch (Exception $e) {
			throw new CoreException('Fatal Error: Error connect '.  $e->getMessage());
		}		
	}

	function update(SqlQuery $query){
		$tables = $query->get('table');
		if(!is_array($tables) || sizeof($tables) == 0){
			throw new CoreException("No calling table()");
		}

		$sql = "UPDATE `".$tables[0][0]."` SET ";

		$data = $query->get('attr');

		if(!$data){
			throw new CoreException('Fatal Error: update not attr');
		}

		$update = array();
		$attr = array();
		foreach ($data as $key => $val) {
			if($key[0] != ':'){
				$attr[":update_gallant_$key"] = $val;
				$update[$key] = ":update_gallant_$key";
			}else{
				$attr[$key] = $val;
			}
		}

		$upd = '';
		foreach ($update as $colon => $val) {
			if($upd) $upd .= ", ";
			$upd .= " `$colon` = $val";
		}
		$sql .= "$upd";

		////////////////// where
		$where = $query->get('where');
		if($where){
			$sql .= " WHERE ".implode(' AND ', $where);
		}
		$query->reAttr($attr);

		return $sql;
	}

	function delete(SqlQuery $query){
		$tables = $query->get('table');
		if(!is_array($tables) || sizeof($tables) == 0){
			throw new CoreException("No calling table()");
		}
		$sql = "DELETE FROM `".$tables[0][0]."` ";

		////////////////// where
		$where = $query->get('where');
		if($where){
			$sql .= " WHERE ".implode(' AND ', $where);
		}

		////////////////// limit
		if(($limit = $query->get('limit')) !== false){
			$sql .= " LIMIT $limit";
			if($offset = $query->get('offset')){
				$sql .= ", $offset";
			}
		}

		return $sql;
	}

	function insert(SqlQuery $query){
		$tables = $query->get('table');
		if(!is_array($tables) || sizeof($tables) == 0){
			throw new CoreException("No calling table()");
		}
		$sql = "INSERT INTO `".$tables[0][0]."` ";

		$attr = $query->get('attr');
		if($attr){
			$attr_keys = array_keys($attr);
			$attr_val = array_values($attr);
			$f = function($val){
				return "`$val`";
			};
			$f2 = function($val){
				return ":$val";
			};

			$sql .= ' ('.implode(', ', array_map($f, $attr_keys)).') ';
			
			$attr_execute = array_map($f2, $attr_keys);
			
			$sql .= " VALUES ( ".implode(', ', $attr_execute)." ) ";

			$attr = array_combine($attr_execute, $attr_val);
		}else{
			$sql .= " () VALUES ()";
		}
		if($attr){
			$query->reAttr($attr);
		}

		return $sql;
	}

	function select(SqlQuery $query){
		$sql = "SELECT ";

		////////////////// colons
		$columns = $query->get('columns');
		if(is_array($columns) && sizeof($columns)){
			$sql .= implode(', ', $columns);
		}else{
			$sql .= '*';
		}

		////////////////// table
		$tables = $query->get('table');
		if(!is_array($tables) || sizeof($tables) == 0){
			throw new CoreException("No calling table()");
		}
		$f_table = function(&$val, $key){
			$name = "$val[0]";
			if($val[1]){
				$name .= " AS `$val[1]`";
			}
			$val = $name;
		};
		array_walk($tables, $f_table);
		$sql .= " FROM ".implode(', ', $tables);

		////////////////// join
		$joins = $query->get('join');
		if($joins){
			$f_join = function(&$val, $key){
				$t = ($val[1]) ? "$val[0] AS `$val[1]`" : "`$val[0]`";
				$val = " LEFT JOIN $t ON $val[2]";
			};
			array_walk($joins, $f_join);
			$sql .= implode(' ', $joins);
		}


		////////////////// where
		$where = $query->get('where');
		if($where){
			$sql .= " WHERE ".implode(' AND ', $where);
		}

		////////////////// group
		$group = $query->get('group');
		if($group){
			$sql .= " GROUP BY $group";
		}

		////////////////// order
		if($orders = $query->get('order')){
			$sql .= " ORDER BY ";
			$sql_order = '';
			foreach($orders as $order){
				if($sql_order) $sql_order .= ', ';
				$sort = ($order[1] == 'ASC') ? 'ASC' : 'DESC';
				$sql_order .= " $order[0] $sort ";
			}
			$sql .= $sql_order;
		}		

		////////////////// limit
		if(($limit = $query->get('limit')) !== false){
			$sql .= " LIMIT $limit";
			if($offset = $query->get('offset')){
				$sql .= ", $offset";
			}
		}

		return $sql;
	}

	function fetch($query_sql, $attr = array()){
		$type = substr($query_sql, 0, 6);
		//p($type, $query_sql, $attr);

		if(!$pdo_query = $this->pdo->prepare($query_sql)){			
			throw new CoreException("DB error query sql: $query_sql");			
		}

		if($attr && sizeof($attr)){
			$exec = $pdo_query->execute($attr);
		}else{
			$exec = $pdo_query->execute();
		}

		$this->report[] = array($type, $query_sql, $attr);
		
		if($type == 'SELECT'){
			$result = $pdo_query->fetchAll(\PDO::FETCH_ASSOC);
			if(!$result){
				return false;
			}else{				
				return $result;
			}
		}else if($type == 'INSERT'){
			$return_id = $this->pdo->lastInsertId();

			if($pdo_query->rowCount()){
				if($return_id) return $return_id;
				return true;
			}
			
			return false;
		}else if($type == 'UPDATE'){
			return $exec;
		}else if($type == 'DELETE'){
			return $pdo_query->rowCount();
		}else{
			throw new CoreException("Not fount handler: $type");
		}
	}

	function debug(){
		return $this->report;
	}
}
