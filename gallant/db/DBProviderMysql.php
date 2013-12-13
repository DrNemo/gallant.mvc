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

class DBProviderMysql{
	private $pdo = false;
	private $count = 0;
	private $report = array();
	private $structure = array();

	function __construct($config){
		if (!class_exists('\PDO')){
			throw new \Gallant\Exceptions\CoreException('Fatal Error: To work needs the support of PDO.');
		}
		try{
			if(!$this->pdo = new \PDO("mysql:host=$config[host];dbname=$config[table]", $config['user'], $config['pass'])) die('Fatal Error: Error connect');
			$this->pdo->query("SET CHARACTER SET $config[character]");		
			$this->pdo->query("SET character_set_client = '$config[character]'");
			$this->pdo->query("SET character_set_connection = '$config[character]'");
			$this->pdo->query("SET character_set_results = '$config[character]'");
		}catch (Exception $e) {
			throw new \Gallant\Exceptions\CoreException('Fatal Error: Error connect '.  $e->getMessage());
		}
		
	}

	function update($DBQuery, $replice){
		$sql = "UPDATE `".$DBQuery['table'][0]['name']."` SET ";
		$data = $DBQuery['attr'];

		if(!$data){
			throw new \Gallant\Exceptions\CoreException('Fatal Error: update not attr');
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

		// where
		if($DBQuery['where']){
			$sql .= " WHERE ".implode(' AND ', $DBQuery['where']);
		}

		///////////////////////////////
		// p($sql, $attr, $data);
		if(!$pdo_query = $this->pdo->prepare($sql)){			
			throw new \Gallant\Exceptions\CoreException("DB error query sql");			
		}

		if($attr){
			$exec = $pdo_query->execute($attr);
		}else{
			$exec = $pdo_query->execute();
		}

		if($exec){
			$this->report[$this->count] = $sql;
			$this->count ++;
			return $exec;
		}else{
			return false;
		}
	}

	function delete($DBQuery){
		$sql = "DELETE FROM `".$DBQuery['table'][0]['name']."` ";

		////////////////// where
		if($DBQuery['where']){
			$sql .= " WHERE ".implode(' AND ', $DBQuery['where']);
		}

		////////////////// limit
		if(isset($DBQuery['limit'])){
			$sql .= " LIMIT $DBQuery[limit]";
			if(isset($DBQuery['ofset'])){
				$sql .= ", $DBQuery[ofset]";
			}
		}

		////////////////// attr
		$attr = false;
		if(isset($DBQuery['attr'])){
			$attr = $DBQuery['attr'];
		}

		////////////////// QUERY
		// p($sql, $attr);
		if(!$pdo_query = $this->pdo->prepare($sql)){			
			throw new \Gallant\Exceptions\CoreException("DB error query sql");			
		}

		if($attr){
			$exec = $pdo_query->execute($attr);
		}else{
			$exec = $pdo_query->execute();
		}
		

		if(!$result = $pdo_query->fetchAll(\PDO::FETCH_ASSOC)){
			return false;
		}else{
			$this->report[$this->count] = $sql;
			$this->count ++;
			return $result;
		}
	}

	function insert($DBQuery, $replice = false){
		$sql = "INSERT INTO `".$DBQuery['table'][0]['name']."` ";
		if($DBQuery['attr']){
			$attr_keys = array_keys($DBQuery['attr']);
			$attr_val = array_values($DBQuery['attr']);
			$f = function($val){
				return "`$val`";
			};
			$f2 = function($val){
				return ":$val";
			};

			$sql .= ' ('.implode(', ', array_map($f, $attr_keys)).') ';
			
			$attr_execute = array_map($f2, $attr_keys);
			
			$sql .= " VALUES ( ".implode(', ', $attr_execute)." );";

			$attr = array_combine($attr_execute, $attr_val);
		}else{
			$sql .= " () VALUES ()";
		}

		// p($sql, $attr);
		if(!$pdo_query = $this->pdo->prepare($sql)){			
			throw new \Gallant\Exceptions\CoreException("DB error query sql");			
		}
		$exec = $pdo_query->execute($attr);
		
		return $this->pdo->lastInsertId();
	}

	function select($DBQuery, $replice = false){
		$sql = "SELECT ";

		////////////////// colons 
		if($DBQuery['columns']){
			$sql .= implode(', ', $DBQuery['columns']);
		}else{
			$sql .= '*';
		}


		////////////////// table
		$f_table = function(&$val, $key){
			$val = "`$val[name]` AS `$val[as]`";
		};
		$table = $DBQuery['table'];
		array_walk($table, $f_table);
		$sql .= " FROM ".implode(', ', $table);


		////////////////// join
		if(isset($DBQuery['join'])){
			$joins = $DBQuery['join'];
			$f_join = function(&$val, $key){
				$t = ($val['as']) ? "`$val[table]` AS `$val[as]`" : "`$val[table]`";
				$val = " LEFT JOIN $t ON $val[on]";
			};
			array_walk($joins, $f_join);
			$sql .= implode(' ', $joins);
		}


		////////////////// where
		if(isset($DBQuery['where'])){
			$sql .= " WHERE ".implode(' AND ', $DBQuery['where']);
		}

		////////////////// group
		if(isset($DBQuery['group'])){
			$sql .= " GROUP BY $DBQuery[group]";
		}

		////////////////// order
		if(isset($DBQuery['order'])){
			$sql .= " ORDER BY ";
			$sql_order = '';
			foreach($DBQuery['order'] as $order){
				if($sql_order) $sql_order .= ', ';
				$sort = ($order[0] == 'asc') ? 'ASC' : 'DESC';
				$sql_order .= " $order[1] $sort ";
			}
			$sql .= $sql_order;
		}		

		////////////////// limit
		if(isset($DBQuery['limit'])){
			$sql .= " LIMIT $DBQuery[limit]";
			if(isset($DBQuery['ofset'])){
				$sql .= ", $DBQuery[ofset]";
			}
		}

		////////////////// attr
		$attr = false;
		if(isset($DBQuery['attr'])){
			$attr = $DBQuery['attr'];
		}

		////////////////// REPLACE
		if($replice){
			$sql = self::replace($sql, $replice);
		}
		
		////////////////// QUERY
		// p($sql, $attr, $replice);
		if(!$pdo_query = $this->pdo->prepare($sql)){			
			throw new \Gallant\Exceptions\CoreException("DB error query sql");			
		}

		if($attr){
			$exec = $pdo_query->execute($attr);
		}else{
			$exec = $pdo_query->execute();
		}
		

		if(!$result = $pdo_query->fetchAll(\PDO::FETCH_ASSOC)){
			return false;
		}else{
			// p($result);
			$this->report[$this->count] = $sql;
			$this->count ++;
			return $result;
		}
	}

	function replace($sql, $replace){
		$keys = array_map(function($val){return "{{{$val}}}";}, array_keys($replace));

		return str_replace($keys, array_values($replace), $sql);
	}
}