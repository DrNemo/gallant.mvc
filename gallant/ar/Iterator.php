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

class Iterator extends \ArrayObject{

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
	*
	* @param function $filter
	* @param array $params
	*
	*	$function = function($val, $key, $params){
	*		$val - значение свойства, 
	*		$key - ключ свойства, 
	*		$params - массив $params
	*		return bool
	*	}
	*/
	function filter($filter, array $params = array()){		
		if(!$this->count()) return array();
		$copy_iter = $this->getArrayCopy();

		// iter to Iterator
		foreach ($copy_iter as $line => $model) {
			
			$data = $model->getData();
			// iter to prop in model
			foreach ($data as $key => $value) {
				// iter to filter
				if(!$filter($value, $key, $params)){
					unset($copy_iter[$line]);
				}				
			}
		}
		return new Iterator($copy_iter);
	}

	
}