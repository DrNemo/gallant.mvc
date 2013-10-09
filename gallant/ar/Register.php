<?
/**
* Gallant\Ar\Register
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Ar;

final class Register{
	private static $db_structure = array();
	private static $pre_sql = array();

	public static function setStructure($model, $structure){
		self::$db_structure[$model] = $structure;
	}

	public static function getStructure($model){
		if(self::$db_structure[$model]){
			return self::$db_structure[$model];
		}
		return false;
	}

	public static function setQuery($model, $query){
		self::$pre_sql[$model] = $query;
	}

	public static function getQuery($model){
		if(self::$pre_sql[$model]){
			return self::$pre_sql[$model];
		}
		return false;
	}

}