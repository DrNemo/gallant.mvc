<?php
/**
* Gallant\Ar\Criteria
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/
namespace Gallant\Ar;

class Criteria{
	private $query = array(
		'attr' => array()
		);

	public function where($where){
		$this->query['where'] = $where;
		return $this;
	}

	public function whereRelation($relat_key, $where){
		$this->query['whereRelation'][$relat_key] = $where;
		return $this;
	}

	public function limit($limit, $offset = false){
		$q = array($limit);
		if($offset){
			$q[] = $offset;
		}
		$this->query['limit'] = $q;
		return $this;
	}

	public function order($column, $order = 'ASC'){
		$this->query['order'][] = array($column, strtoupper($order));
		return $this;
	}

	public function orderRelation($relat_key, $column, $order = 'ASC'){
		$this->query['orderRelation'][] = array($relat_key, $column, strtoupper($order));
		return $this;
	}

	public function attr($attr){
		$this->query['attr'] = array_merge($this->query['attr'], $attr);
		return $this;
	}

	public function group($column){
		$this->query['group'] = $column;
		return $this;
	}

	public function get($key){
		if(isset($this->query[$key])){
			return $this->query[$key];
		}
		return false;
	}
	
}