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

namespace Gallant\DB;

class DBProviderMysql{
	private $pdo = false;
	private $count = 0;
	private $report = array();
	private $structure = array();

	function __construct($config){
		if (!class_exists('PDO')){
			throw new \Gallant\Exceptions\CoreException('Fatal Error: To work needs the support of PDO.');
		}
		$this->pdo = new \PDO("mysql:host=$config[host];dbname=$config[table]", $config['user'], $config['pass']);
		if(!$this->pdo){
			throw new \Gallant\Exceptions\CoreException('Fatal Error: Error connect');
		}
		$this->pdo->query("SET CHARACTER SET $config[character]");		
		$this->pdo->query("SET character_set_client = '$config[character]'");
		$this->pdo->query("SET character_set_connection = '$config[character]'");
		$this->pdo->query("SET character_set_results = '$config[character]'");
	}

	function update($DBQuery){
		$sql = "UPDATE `".$DBQuery['table'][0]['name']."` SET ";
		$data = $DBQuery['attr'];

		if(!$data){
			throw new \Gallant\Exceptions\CoreException('Fatal Error: update not attr');
		}
		$f = function($val, $key, $data){
			if($key[0] != ':'){
				$data['attr'][":$key"] = $val;
				$data['update'][$key] = ":$key";
			}else{
				$data['attr'][$key] = $val;
			}
		};
		$update = $attr = array();
		array_walk($data, $f, array('update' => &$update, 'attr' => &$attr));
		$upd = '';
		foreach ($update as $colon => $val) {
			if($upd) $upd .= ", ";
			$upd .= " $colon = $val";
		}
		$sql .= "$upd";

		// where
		if($DBQuery['where']){
			$sql .= " WHERE ".implode(' AND ', $DBQuery['where']);
		}

		//p($sql, $attr);
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

	}

	function insert($DBQuery){		
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

    	if(!$pdo_query = $this->pdo->prepare($sql)){			
			throw new \Gallant\Exceptions\CoreException("DB error query sql");			
		}
		$exec = $pdo_query->execute($attr);
		
		return $this->pdo->lastInsertId();
	}

	function select($DBQuery){
		$sql = "SELECT ";

		////////////////// colons 
		if($DBQuery['columns']){
			$_sql = "";
			$f_colon = function(&$val, $key, $dop){
				$val = "`$dop[as]`.`$val` AS `$dop[pref]$val`";
			};
			foreach ($DBQuery['columns'] as $column) {
				if($_sql) $_sql .= ', ';
				$columns = $column['column'];
				array_walk($columns, $f_colon, array('as'=>$column['table_as'], 'pref'=>$column['column_pref']));
				$_sql .= implode(', ', $columns);
			}
			$sql .= $_sql;
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
		if($DBQuery['join']){
			$joins = $DBQuery['join'];
			$f_join = function(&$val, $key){
				$t = ($val['as']) ? "`$val[table]` AS `$val[as]`" : "`$val[table]`";
				$val = " LEFT JOIN $t ON $val[on]";
			};
			array_walk($joins, $f_join);
			$sql .= implode(' ', $joins);
		}


		////////////////// where
		if($DBQuery['where']){
			$sql .= " WHERE ".implode(' AND ', $DBQuery['where']);
		}

		
		////////////////// attr
		$attr = false;
		if($DBQuery['attr']){
			$attr = $DBQuery['attr'];
		}
		
		////////////////// QUERY
		//p($sql, $attr);
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
		}

		if($result){
			$this->report[$this->count] = $sql;
			$this->count ++;
			return $result;
		}else{
			return false;
		}
	}	
}