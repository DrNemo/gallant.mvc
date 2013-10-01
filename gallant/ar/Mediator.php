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

class Mediator{
	protected $data = false;
	protected $cursor = 0;
	protected $count = 0;

	function push($obj){
		$this->data[] = $obj;
		$this->count ++;
	}

	function remove(){
		unset($this->data[$this->cursor]);
	}

	function next(){
		$this->cursor ++;
		if($this->data[$this->cursor]){
			return $this->data[$this->cursor];
		}
	}

	function prev(){
		$this->cursor --;
		if($this->data[$this->cursor]){
			return $this->data[$this->cursor];
		}
	}

	function reset(){
		$this->data = array_values($this->data);
		$this->cursor = 0;
		return $this;
	}

	function all(){
		if($this->data){
			return array_values($this->data);
		}
		return false;
	}

	function last(){
		$data = $this->data;
		if($r = array_pop($data)){
			return $r;
		}
		return false;
	}

	function first(){
		if(sizeof($this->data) == 0) return false;
		$data = $this->data;
		if($r = array_shift($data)){
			return $r;
		}
		return false;
	}

	function search(){

	}

	function sum(){

	}
}