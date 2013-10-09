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
use \G as G;

spl_autoload_register(array('Gallant\AutoLoading', 'loadingCore'));
spl_autoload_register(array('Gallant\AutoLoading', 'loadingSite'));

class AutoLoading{

	public static function loadingCore($inc){
		$routs = array_filter(explode('\\', $inc.'\\'),'trim');
		
		$class = array_pop($routs);
		$routs = array_map('strtolower', $routs);
		$type = array_shift($routs);

		if($class == 'G'){
			include_once GALLANT_CORE.'/App.php';
			return true;
		}else if($class == 'Entry' && $type != 'gallant'){
			return false;			
		}		

		if($type != 'gallant'){
			return false;
		}
		$path = '/';
		if($routs){
			$path .= implode('/', $routs).'/';
		}
		
		$path = GALLANT_CORE.$path.$class.'.php';
		if(is_file($path)){
			include_once $path;
			return true;
		}
		return false;
	}

	public static function loadingSite($inc){
		$routs = array_filter(explode('\\', $inc.'\\'));
		$type = strtolower(array_shift($routs));

		$path = G::getPath($type);

		if($type == 'entry'){
			if(is_file(G::getPath('entry'))){
				include_once G::getPath('entry');
				
				return true;
			}
		}

		if(!$path){
			throw new \Gallant\Exceptions\CoreException('error loadingSite : '.$type);
		}
		
		$class = array_pop($routs);
		$routs = array_map('strtolower', $routs);

		$p2 = implode('/', $routs).'/';
		if(is_array($path)){
			foreach ($path as $val) {
				if(is_file($val.$p2.$class.'.php')){
					include_once $val.$p2.$class.'.php';
					return true;
				}
			}
		}else if(is_file($path.$p2.$class.'.php')){
			include_once $path.$p2.$class.'.php';
			return true;
		}		
		return false;
	}
}