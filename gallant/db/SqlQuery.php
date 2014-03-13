<?
/**
* Gallant\DB\DBQuery
* Класс конструктор для sql запросов. Все обращения к БД рекамендуется выполнять через этот класс
* Реализован плавучий интерфейс
*
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\DB;

use \G;
use \Gallant\Exceptions\CoreException;

class SqlQuery{
	protected $query = false;
	protected $pref = '';
	protected $config;
	protected $provider = false;

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
		$this->query = array();
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
		if($table instanceof SqlQuery){
			$this->query['table'][] = array(
				 '('.$table->sql().')',
				(($as) ? $as : '')
				);
		}else{
			$table = $this->tableName($table);
			$this->query['table'][] = array(
				$table,
				($as) ? $as : ''
				);			
		}
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
	function columns($column){
		if(!isset($this->query['columns'])) $this->query['columns'] = array_values($column);
		else $this->query['columns'] = array_merge($this->query['columns'], array_values($column));
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
	function merge(SqlQuery $query){
		$pre_sql = $query->getRequest();
		$this->query = array_merge_recursive($this->query, $pre_sql);
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
			$table = $table[0];
		}

		if($table instanceof SqlQuery){
			$table = "(".$table->sql().")";
		}else{
			$table = $this->tableName($table);
		}
		$this->query['join'][] = array(
			$table, (($as) ? $as : 't'.$this->table_num), $on
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
		if(!isset($this->query['where'])) $this->query['where'] = array();
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

	function order($colomn, $sort = 'asc'){
		$sort = strtoupper($sort);
		if(!isset($this->query['order'])){
			$this->query['order'] = array();
		}
		if(is_array($colomn)){
			$this->query['order'] = array_merge($this->query['order'], $colomn);
		}else if(strlen($colomn)){
			$this->query['order'][] = array($colomn, $sort);
		}else{
			throw new CoreException("No name colomn order(colomn [, order = 'ASC'])");
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
		return $this->order($colomn, 'asc');
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
	* group 
	* 
	* @param string $colomn
	* @return $this
	*/
	function group($colomn){
		$this->query['group'] = $colomn;
		return $this;
	}

	/**
	* attr установка отребутов запроса 
	* 
	* @param array $attr array(':col1' => $val, ':mail' => $mail, ...)
	* @return $this
	*/
	function attr($attr){
		if(isset($this->query['attr'])) $this->query['attr'] = array_merge($this->query['attr'], $attr);
		else $this->query['attr'] = $attr;
		return $this;
	}

	/**
	* attr установка отребутов запроса 
	* 
	* @param array $attr array(':col1' => $val, ':mail' => $mail, ...)
	* @return $this
	*/
	function reAttr($attr){
		$this->query['attr'] = $attr;
		return $this;
	}

	/**
	*
	*
	**/
	function get($key){
		if(isset($this->query[$key])){
			return $this->query[$key];
		}
		return false;
	}

	private $query_sql = false;
	/**
	* select отправляет запрос на выполнение выборки
	* 
	* @return mixid возвращяет результат выборки
	*/
	final function select(){
		$sql = G::DB($this->provider)->select($this);
		return G::DB($this->provider)->fetch($sql, $this->get('attr'));
	}	

	/**
	* insert отправляет запрос на выполнение вставки
	* 
	* @return mixid первичные ключи новой записи
	*/
	final function insert(){
		$sql = G::DB($this->provider)->insert($this);
		return G::DB($this->provider)->fetch($sql, $this->get('attr'));
	}

	/**
	* update отправляет запрос на выполнение обновления
	* 
	* @return mixid количество затронутых рядов
	*/
	final function update(){
		$sql = G::DB($this->provider)->update($this);
		return G::DB($this->provider)->fetch($sql, $this->get('attr'));
	}

	/**
	* @todo delete отправляет запрос на выполнение удаления
	* 
	* @return mixid количество затронутых рядов
	*/
	final function delete($replice = false){
		$sql = G::DB($this->provider)->delete($this);
		return G::DB($this->provider)->fetch($sql, $this->get('attr'));
	}


	function sql($method = 'select'){
		return G::DB($this->provider)->$method($this);
	}
}