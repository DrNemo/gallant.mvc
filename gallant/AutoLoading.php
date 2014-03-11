<?
/**
* Gallant\AutoLoading
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant;
use \G;
use \Gallant\Exceptions\CoreException;

spl_autoload_register(array('Gallant\AutoLoading', 'loadingCore'));
spl_autoload_register(array('Gallant\AutoLoading', 'loadingSite'));

class AutoLoading{

	public static function loadingCore($inc){
		//p('loadingCore:' . $inc);
		$routs = array_filter(explode('\\', $inc),'trim');

		$class = array_pop($routs);
		$routs = array_map('strtolower', $routs);
		$type = array_shift($routs);

		if($class == 'G'){
			include_once GALLANT_CORE.'/App.php';
		}else if($type != 'gallant'){
			return false;			
		}
		
		$path = DIRECTORY_SEPARATOR;
		if($routs){
			$path .= implode(DIRECTORY_SEPARATOR, $routs) . DIRECTORY_SEPARATOR;
		}
		$path = GALLANT_CORE.$path.$class.'.php';

		if(is_file($path)){
			include_once $path;
		}
		return false;
	}

	static public function loadingSite($inc){
		//p('loadingSite: ' . $inc);
		$routs = array_filter(explode('\\', $inc.'\\'));
		$type = strtolower(array_shift($routs));

		if($type == 'entry'){
			if(is_file(G::getPath('entry'))){
				include_once G::getPath('entry');				
				return true;
			}
		}		

		$paths = G::getPath($type);
		if(!$paths){
			throw new CoreException("error loadingSite : $type ($inc)");
		}
		if(!is_array($paths)){
			$paths = array($paths);
		}
		
		$class = array_pop($routs);
		$routs = array_map('strtolower', $routs);

		$to_path = implode(DIRECTORY_SEPARATOR, $routs);
		if($to_path){
			$file = self::dirSep($to_path .DIRECTORY_SEPARATOR . $class . '.php');
		}else{
			$file = $class . '.php';
		}

		foreach ($paths as $path) {	
			if(is_file($path.$file)){
				include_once $path.$file;
				return true;
			}
		}
	}

	function dirSep($path){
		$dir_pre_sep = array('\\', '/');
		return str_replace($dir_pre_sep, DIRECTORY_SEPARATOR, $path);
	}
}