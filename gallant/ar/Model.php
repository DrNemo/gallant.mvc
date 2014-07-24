<?
/**
* Gallant\Ar\Model
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Ar;

use \G;
use \Gallant\Exceptions\ArException;
use \Gallant\Exceptions\CoreException;

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
	static public function structure(){
		throw new ArException('override function structure()');
	}

	/**
	* provider переопределите метод с указанием провайдера БД из файла config.php
	* 
	* @return string
	*/
	static public function provider(){
		return array_shift(array_keys(G::getConfig('db')));
	}

	/**
	* table переопределите метод с указанием таблицы в БД
	* 
	* @return string
	*/
	static public function table(){
		throw new ArException('override function table()');
	}

	/**
	* primaryKey переопределите метод с указанием первичного ключа 
	* return 'id'
	* если их несколько
	* return array('id1', 'id2')
	* 
	* @return mixed
	*/
	static public function primaryKey(){
		throw new ArException('override function primaryKey()');
	}

	/**
	* relations переопределите метод с указанием связей с другими моделями
	*
	* @return array
	*/
	static public function relations(){
		return array();
	}

	/**
	* __isset проверяет наличее аргумента у модели
	* 
	* @param sting
	* @param mixed
	*/
	public function __isset($key){
		if(isset($this->data[$key])) return true;
		if(isset($this->data_update[$key])) return true;
		return false;
	}

	/**
	* __set обрабатывает $model->public_key = val устанавливает новое значение
	* 
	* @param sting
	* @param mixed
	*/
	public function __set($key, $val){
		if(isset($this->data[$key]) || is_null($this->data[$key])){
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
	* original $model->original('public_key') возвращяет оригинальное значение, до его изменения метотами (__set, attr, setData)
	* 
	* @param sting
	* @return mixed
	*/
	public function original($key){
		if(isset($this->data[$key])){
			return $this->data[$key];
		}else{
			return false;
		}
	}

	/**
	* attr установка и получение свойств модели
	* $model->attr(); вернет свойства модели
	* $model->attr(array('key' => 'value')); устанавливает свойства модели
	* @param array
	*/
	public function attr($data = false){
		if(!$data){
			return $this->getData();
		}else{
			$this->setData($data);
		}
	}

	public function setData($data){
		if(is_array($data)){
			$this->data_update = array_merge($this->data_update, $data);
		}
		return $this;
	}

	/**
	* getData возвращяет все свойства модели в виде массива
	* 
	* @return array
	*/
	public function getData(){
		return array_merge($this->data, $this->data_update);
	}

	/**
	* criteria возвращяет подготовленный класс запроса для метода fetch
	* @return class Criteria
	*/
	static function criteria(){
		return new Criteria;
	}

	/**
	* onLoad событие вызывается после загрузки модели из бд
	*/ 
	protected function onLoad(){}

	/**
	* onBeforeSave событие вызывается перед сохранения модели в бд
	*/
	protected function onBeforeSave(){}

	/**
	* onAfterSave событие вызывается после сохранения модели в бд
	*/
	protected function onAfterSave(){}

	/**
	* onDelete событие вызывается после удаления модели
	*/
	protected function onDelete(){}
}