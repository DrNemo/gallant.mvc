<?
/**
* Gallant\Ar\Iterator
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/
namespace Gallant\Ar;

use \ArrayObject;

class Iterator extends ArrayObject{

	/**
	*	
	*
	*/
	function first(){
		if(!$this->count()) return false;
		$data = $this->getArrayCopy();
		if($r = array_shift($data)){
			return $r;
		}
		return false;
	}

	/**
	*	
	*
	*/
	function last(){
		if(!$this->count()) return false;
		$data = $this->getArrayCopy();
		if($r = array_pop($data)){
			return $r;
		}
		return false;
	}

	/**
	* filter возвращяет отфильтрованный Iterator
	* фильтруются данные модели
	*
	* @param function $filter
	* @param array $params
	*
	*	$function = function($model, $index, $params){
	*		$model - модель
	*		$index - ключ
	*		$params - массив $params
	*		return bool
	*	}
	* 
	*/
	function filter($filter, array $params = array()){		
		if(!$this->count()) return new Iterator;
		$copy_iter = $this->getArrayCopy();

		// iter to Iterator
		foreach ($copy_iter as $index => $model) {
			if($filter($model, $index, $params) !== true){
				unset($copy_iter[$index]);
			}
		}
		return new Iterator($copy_iter);
	}

	/**
	* walk применяет произвольную функцию ко всем элементам Iterator
	*
	* @param function $function
	* @param array $params
	*
	*	$function = function($model, $params){
	*		$model - модель, 
	*    	$index - ключ
	*		$params - массив $params
	*	}
	*/
	function walk($function, array $params = array()){
		if(!$this->count()) return new Iterator;
		$copy_iter = $this->getArrayCopy();

		foreach ($copy_iter as $index => $model) {
			$function($model, $index, $params);
		}
		$copy_iter = array_filter($copy_iter);
		return new Iterator($copy_iter);
	}

	/**
	* merge объединяет текущий Iterator с переданным
	*
	* @param Iterator $Iterator
	*/
	function merge(Iterator $Iterator){
		foreach ($Iterator as $value) {
			$this->append($value);
		}
	}

	/**
	* slice выполняет срез 
	*
	* @param int $offset 
	* @param int $length
	*
	*	$function = function($model, $params){
	*		$model - модель, 
	*		$params - массив $params
	*	}
	*/
	function slice($offset, $length = false){
		$copy_iter = $this->getArrayCopy();
		$new_iter = array_slice($copy_iter, $offset, $length);
		return new Iterator($new_iter);
	}
}