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

class Register{
	protected function __construct(){}
	protected function __clone(){}

	private static $db_structure = array();
	private static $pre_sql = array();
	private static $replace = array();

	public static function setStructure($model, $structure){
		self::$db_structure[$model] = $structure;
	}

	public static function getStructure($model){
		if(isset(self::$db_structure[$model])){
			return self::$db_structure[$model];
		}
		return false;
	}
	

	public static function setQuery($model, $query){
		self::$pre_sql[$model] = $query;
	}

	public static function getQuery($model){
		if(isset(self::$pre_sql[$model])){
			return self::$pre_sql[$model];
		}
		return false;
	}


	public static function setReplace($model, $replace){
		self::$replace[$model] = $replace;
	}

	public static function getReplace($model){
		if(isset(self::$replace[$model])){
			return self::$replace[$model];
		}
		return false;
	}

}