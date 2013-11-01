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

	protected static $model_pre_data = array();
	protected static $model_structure = array();

	public static function set($model, $pre_data){
		self::$model_pre_data[$model] = $pre_data;
	}

	public static function get($model){
		if(isset(self::$model_pre_data[$model])){
			return self::$model_pre_data[$model];
		}
		return false;
	}

	public static function setStructure($model, $struct){
		self::$model_structure[$model] = $struct;
	}

	public static function getStructure($model){
		if(isset(self::$model_structure[$model])){
			return self::$model_structure[$model];
		}
		return false;
	}

}