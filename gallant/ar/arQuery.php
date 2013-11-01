<?
/**
* Gallant\Ar\arQuery
* Расширенеи класса DBQuery, для работы с ar
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/
namespace Gallant\Ar;

class arQuery extends \Gallant\DB\DBQuery{

	function columns($column, $table_as = false){
		$colons = array();
		foreach ($column as $name) {
			$colons[] = "`$table_as`.`$name` AS `{$table_as}_{$name}`";
		}
		$this->query['columns'][] = implode(', ', $colons);
		return $this;
	}
}