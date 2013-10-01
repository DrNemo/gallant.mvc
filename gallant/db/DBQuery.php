<?
/**
* DBQuery
* Класс конструктор для sql запросов. Все обращения к БД рекамендуется выполнять через этот класс
* Реализован плавучий интерфейс
*
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\DB;
use \G as G;

class DBQuery{
	private $query = false;
	private $pref = '';
	private $config;
	private $table_num = 0;
	private $provider = false;

	/**
	* __construct
	* 
	* @param string $provider тип подключения к Базе Данных, берется и config.php раздел db, 
	* если не указан будет выбран первый вариант
	*/
	function __construct($provider = false){
		$db = G::getConfig('db');
		if(!$provider){
			$provider = array_shift(array_keys($db));
		}
		$this->pref = $db[$provider]['pref'];
		$this->config = $db[$provider];
		$this->provider = $provider;
	}

	/**
	* tableName возвращяет реальное название таблицы в БД с префиксом
	* 
	* @param string $table 
	* @return string
	*/
	function tableName($table){
		return ($table[0] == '!') ? substr($table, 1) : $this->pref.$table;
	}

	/**
	* table устанавливает таблицу в запросе
	* 
	* @param string $table название таблицы, если в начале стоит !, то к имени таблицы не будет прибавлен префикс из настроек подключения 
	* @param string $as 
	* @return $this
	*/
	function table($table, $as = false){
		$table = $this->tableName($table);
		$this->query['table'][] = array(
			'name' => $table,
			'as' => ($as) ? $as : 't'.$this->table_num
			);
		$this->table_num ++;
		return $this;
	}

	/**
	* columns устанавливает колонки для выборки, если не установить будут возвращены все колонки
	* 
	* @param string $column список колонок array('id', 'login', 'password', ...)
	* @param string $table_as
	* @param string $pref префикс для колонок
	* @return $this
	*/
	function columns($column, $table_as = false, $pref = false){
		$columne = array(
			'column' => $column,
			'table_as' => $table_as,
			'column_pref' => $pref
			);
		if(!$this->query['columns']) $this->query['columns'][] = $columne;
		else $this->query['columns'] = array_merge($this->query['columns'], array($columne));
		return $this;
	}

	/**
	* getRequest возвращяет массив подготовленного запроса
	* 
	* @return array
	*/
	function getRequest(){
		return $this->query;
	}

	/**
	* merge объединяет запросы в 1
	* 
	* @param object $query \Gallant\DB\DBQuery подготовленный запрос
	* @return $this
	*/
	function merge(\Gallant\DB\DBQuery $query){
		$pre_sql = $query->getRequest();
		$this->query = array_merge($this->query, $pre_sql);
		return $this;
	}

	/**
	* join добавляет  join к запросу
	* 
	* @param mixed $table:
	* 1. array array('table_name', 'table_as')
	* 1. string 'table_name'
	* @param string $on условия join: t1.col1 = t2.col5 ...
	* @return $this
	*/
	function join($table, $on){		
		$as = false;
		if(is_array($table)){			
			$as = $table[1];
			$table = $this->tableName($table[0]);
		}else{
			$table = $this->tableName($table);
		}
		$this->query['join'][] = array(
			'table' => $table,
			'as' => ($as) ? $as : 't'.$this->table_num,
			'on' => $on
			);
		$this->table_num ++;
		return $this;
	}

	/**
	* where добавляет условия к запросу
	* 
	* @param string $where условия where: col1 = :col1 AND mail = :mail
	* @return $this
	*/
	function where($where){
		if(!$this->query['where']) $this->query['where'] = array();
		$this->query['where'][] = $where;
		return $this;
	}

	/**
	* limit 
	* 
	* @param int $limit
	* @param int $offset
	* @return $this
	*/
	function limit($limit, $offset = false){
		$this->query['limit'] = $limit;
		if($offset){
			$this->query['offset'] = $offset;
		}
		return $this;
	}

	/**
	* orderAsc 
	* 
	* @param string $colomn
	* @return $this
	*/
	function orderAsc($colomn){
		$this->query['order'][] = array('asc', $colomn);
		return $this;
	}

	/**
	* orderDesc 
	* 
	* @param string $colomn
	* @return $this
	*/
	function orderDesc($colomn){
		$this->query['order'][] = array('desc', $colomn);
		return $this;
	}

	/**
	* attr установка отребутов запроса 
	* 
	* @param array $attr array(':col1' => $val, ':mail' => $mail, ...)
	* @return $this
	*/
	function attr($attr){
		if($this->query['attr']) $this->query['attr'] = array_merge($this->query['attr'], $attr);
		else $this->query['attr'] = $attr;
		return $this;
	}

	/**
	* select отправляет запрос на выполнение выборки
	* 
	* @return mixid возвращяет результат выборки
	*/
	final function select(){
		$this->query['type'] = 'select';
		return G::DB($this->provider)->select($this->query);
	}	

	/**
	* insert отправляет запрос на выполнение вставки
	* 
	* @return mixid первичные ключи новой записи
	*/
	final function insert(){
		$this->query['type'] = 'insert';
		$res = G::DB($this->provider)->insert($this->query);
		return $res;
	}

	/**
	* update отправляет запрос на выполнение обновления
	* 
	* @return mixid количество затронутых рядов
	*/
	final function update(){
		$this->query['type'] = 'update';
		return G::DB($this->provider)->update($this->query);
	}

	/**
	* @todo delete отправляет запрос на выполнение удаления
	* 
	* @return mixid количество затронутых рядов
	*/
	final function delete(){
		$this->query['type'] = 'delete';
		
	}
}