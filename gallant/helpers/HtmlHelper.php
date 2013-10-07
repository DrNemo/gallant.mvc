<?
/**
* HtmlHelper
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Helpers;
use \G as G;

class HtmlHelper{

	/**
	* printError выводит ошибки созданные методом G::setError в обрамление <div> с указанным css классом
	* 
	* @param mixed
	* @param string
	* @return string html
	*/
	function printError($code, $class = 'error'){
		if(!is_array($code)) $code = array($code);
		$html = '';
		foreach ($code as $c) {
			if($text = G::getError($c)){
				$html .= "<div id=\"$c\" class=\"$class\">$text</div>";
			}
		}
		return $html;
	}

	/**
	* select формирует option`s из массива array(index => value)
	* 
	* @param array
	* @param mixed
	* @return string html
	*/
	function select($list, $id = false){
		p($list);
		if(!$list) return;
	    foreach($list as $index => $val){
	        if(!$id){
	        	$list.='<option value="'.$index.'">'.$val.'</option>';
	        }else{
	            if($index == $id) $list.='<option value="'.$index.'" selected="selected">'.$val.'</option>';
	            else $list.='<option value="'.$index.'">'.$val.'</option>';
	        }
	    }
	    return $list;
	}

	/**
	* @todo
	*/
	function table(Gallant\Ar\Mediator $mediator){

	}
}