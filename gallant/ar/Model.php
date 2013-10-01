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

class Model extends Builder{	
		
	/**
	* structure переопределите метод с указанием структуры модели
	* return array(
	*	'public_key' => array( - внешнее имя параметра в моделе $model->public_key
	*		'db' => 'db_key' - название соответствующей солонки в БД
	*	)
	*)
	* 
	* @return array 
	*/
	public function structure(){
		return false;
	}

	/**
	* provider переопределите метод с указанием провайдера БД из файла config.php
	* 
	* @return string
	*/
	public function provider(){
		return array_shift(array_keys(G::getConfig('db')));
	}

	/**
	* table переопределите метод с указанием таблицы в БД
	* 
	* @return string
	*/
	public function table(){
		return false;
	}

	/**
	* primaryPk переопределите метод с указанием первичного ключа 
	* return 'id'
	* если их несколько
	* return array('id1', 'id2')
	* 
	* @return mixed
	*/
	public function primaryPk(){
		return false;
	}

	/**
	* relations переопределите метод с указанием связей с другими моделями
	* @todo 
	* array(
	* 	'model_key' => array( - публичный ключ связанной модели
	*		'model' => '\Model\Module', - полное имя класса модели
	*		'my_column' => array('user_in_module', 'user_id'),
	*		'relation' => 'ONE_TO_BUNCH', тип связи
	*		'his_column' => 'module_id'
	*	), 
	*	...
	* )
	*
	* @return array
	*/
	public function relations(){
		return false;
	}

	/**
	* __set обрабатывает $model->public_key = val устанавливает новое значение
	* 
	* @param sting
	* @param mixed
	*/
	public function __set($key, $val){		
		if(isset($this->db_structure[$key])){
			$this->data_update[$key] = $val;
		}
	}

	/**
	* __get обрабатывает $model->public_key возвращяет текущие значение
	* 
	* @param sting
	* @return mixed
	*/
	public function __get($key){
		if(isset($this->data_update[$key])){
			return $this->data_update[$key];
		}else if(isset($this->data[$key])){
			return $this->data[$key];
		}else{
			return false;
		}
	}

	/**
	* getOriginal $model->getOriginal('public_key') возвращяет оригинальное значение
	* 
	* @param sting
	* @return mixed
	*/
	public function getOriginal($key){
		if(isset($this->data[$key])){
			return $this->data[$key];
		}else{
			return false;
		}
	}

	/**
	* attr установка новых значений array('public_key' => val, ...)
	* 
	* @param array
	*/
	public function attr($data){
		if(is_array($data)){
			$this->data_update = array_merge($this->data_update, $data);
		}
	}

	/**
	* related возвращяет связаные модели
	*
	* @param string 'model_key'
	* @return object Gallant\Ar\Mediator
	*/
	public function related($rel){
		$rels = $this->relations();
		if(!$rels[$rel]){
			return false;
		}
		if(!$this->parent_models[$rel]){
			// load parent model
		}
		return $this->parent_models[$rel];
	}
}