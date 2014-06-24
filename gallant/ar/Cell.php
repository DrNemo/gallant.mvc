<?php
/**
* Gallant\Ar\Cell
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/
namespace Gallant\Ar;

class Cell{

	private $value = false;
	private $value_original = false;

	private $key = false;

	function __construct($key, $value){
		$this->key = $key;
		$this->value = $this->value_original = $value;
	}

	
}