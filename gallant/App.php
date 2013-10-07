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

class G{
	private function __construct(){}
    private function __clone(){}

    private static $config = array();

    private static $request = array();

    private static $control = false;
    private static $action = false;

    private static $template = false;
    private static $_route = false;
    private static $helper_lang = false;

    private static $system_path = false;

    public static $filter = array();
	
	public static function version(){
		return "0.0.2 pre-alfa";
	}
	/**
	* G::init
	* 
	* @param string $config_file путь к файлу конфига
	*/
	public static function init($config_file){
		session_start();

		self::$filter['html'] = function(&$v,$k,$filter){
            if(!is_array($v)){
            	$v = htmlspecialchars($v);
            }else array_walk($v, $filter, $filter);
        };

        self::$request = array('post' => $_POST, 'get' => $_GET);

		$def_config = include GALLANT_CORE.\Gallant\GConst::DEFAULT_CONFIG;
		if(!is_array($def_config)){
			$_path = \Gallant\GConst::DEFAULT_CONFIG;
			throw new \Gallant\Exceptions\CoreException('not default config file '.$_path);
		}
		if(!is_file($config_file)){
			throw new \Gallant\Exceptions\CoreException('not config file');
		}
		$app_config = include $config_file;
		if(!is_array($app_config)){
			throw new \Gallant\Exceptions\CoreException('error data config file');
		}
		
		self::$config = array_replace_recursive($def_config, $app_config);

		self::$helper_lang = new \Gallant\Helpers\Lang(self::$config['site']['lang']);
		
		self::$template = new \Gallant\Components\Template();

		self::$_route = new \Gallant\Components\Route();	

		$entry = false;
		if(class_exists('Entry')){
			Entry::init();
			$entry = true;
		}

		self::$_route->route();	

		if($entry){
			Entry::load();
		}


		$control = self::getControl();
		$action = self::getAction();

		$apply = new $control();
		$result = $apply->$action();

		if($entry){
			Entry::render();
		}

		self::template()->render($result);

		if($entry){
			Entry::destroy();
		}
		/**
		* @todo destroy
		*/ 
	}

	/**
	* route
	* 
	* @return object Gallant\Components\Template
	*/
	public static function route(){
		return self::$_route;
	}

	/**
	* template
	* 
	* @return object Gallant\Components\Template
	*/
	public static function template(){
		return self::$template;
	}

	/**
	* lang
	* 
	* @return object Gallant\Helpers\Lang
	*/
	public static function lang(){
		return self::$helper_lang;
	}

	/**
	* getConfig
	* 
	* @param string $key ключ конфига
	* @return mixed
	*/
	public static function getConfig($key){
		if(isset(self::$config[$key])){
			return self::$config[$key];
		}
		return false;
	}

	private static $registr = array();

	/**
	* set
	* 
	* @param string $key произвольный ключ
	* @param mixed $val произвольное значение
	*/
	public static function set($key, $val){
		self::$registr[$key] = $val;
	}

	/**
	* get
	* 
	* @param string $key произвольный ключ указанный в G::set
	* @return mixed
	*/
	public static function get($key){
		if(isset(self::$registr[$key])){
			return self::$registr[$key];
		}
		return false;
	}

	/**
	* getPath
	* 
	* @param string $path_key произвольный ключ указанный в config.php в разделе path
	* @return mixed
	*/
	public static function getPath($path_key){
		if(!self::$system_path){
			$config_path = self::getConfig('path');

			$path_param = array(
				'%site%' => $config_path['site'],
				'%root%' => FOLDER_ROOT,
				);
			
			$not_array = array('site', 'lang', 'entry');
			foreach($config_path as $key => $value) {
				if(in_array($key, $not_array) && is_array($value)){
					throw new \Gallant\Exceptions\CoreException('Config (path=>'.$key.'): can not be an array');
				}
				$path[$key] = str_replace(array_keys($path_param), array_values($path_param), $value);
			}
			$f = function(&$v, $k, $f){
				if(is_array($v)){
					array_walk($v, $f, $f);
				}else{
					if($v[strlen($v) - 1] != '/' && substr($v, -4) != '.php') $v = $v.'/';
				}
			};
			array_walk($path, $f, $f);
			self::$system_path = $path;
		}		
		if(isset(self::$system_path[$path_key])){
			return self::$system_path[$path_key];
		}
		return false;
	}

	/**
	* getPath
	* 
	* @param string $type тип запроса (get, post)
	* @return boolean
	*/
	public static function isRequest($type){
		if(sizeof(self::$request[$type]) > 0) return true;
		return false;
	}

	/**
	* getRequest
	* 
	* @param string $type тип запроса (get, post)
	* @param string $key имя парметра: $_POST['my_key'] => $key = my_key
	* @param string $filter тип фильтра
	* @return mixed
	*/
	public static function getRequest($type, $key, $filter = 'html'){
		$type = strtolower($type);
		if($type != 'post' && $type != 'get'){
			throw new \Gallant\Exceptions\CoreException('error type Request in function G::getRequest($type, $key, $filter)');
		}
		if(isset(self::$request[$type][$key])){
			$req = array(self::$request[$type][$key]);
			if($filter && isset(self::$filter[$filter])){
				array_walk($req, self::$filter[$filter], self::$filter[$filter]);
			}
			if(!$req[0]) return true;
			return $req[0];
		}
		return false;
	}

	/**
	* getRequest
	* 
	* @param string $key ключ сессии
	* @return mixed
	*/
	public static function getSession($key){
		if(isset($_SESSION[$key])){
			return $_SESSION[$key];
		}
		return false;
	}

	/**
	* setSession
	* 
	* @param string $key ключ сессии
	* @param mixed $value значение сессии
	* @param boolean $close защита данных от перезаписи
	* @return boolean
	*/
	public static function setSession($key, $value, $close = false){
		if(!$_SESSION['gallant_system']['close_session'][$key]){
			if($close){
				$_SESSION['gallant_system']['close_session'][$key] = true;
			}
			$_SESSION[$key] = $value;
			return true;
		}
		return false;
	}

	/**
	* removeSession
	* 
	* @param string $key ключ сессии
	*/
	public static function removeSession($key){
		unset($_SESSION['gallant_system']['close_session'][$key]);
		unset($_SESSION[$key]);
	}

	/**
	* setControl
	*
	* Изменение текущего контроллера
	* 
	* @param string $control имя контроллера
	*/
	public static function setControl($control){
		self::$_route->setControl($control);
	}

	/**
	* setControl
	*
	* Возвращение текущего контроллера
	* 
	* @return string $control имя контроллера
	*/
	public static function getControl(){
		return self::$_route->getControl();
	}

	/**
	* setAction
	*
	* Изменение текущего экшена
	* 
	* @param string $action имя экшена
	*/
	public static function setAction($action){
		self::$_route->setAction($action);
	}

	/**
	* getAction
	*
	* Возвращение текущего экшена
	* 
	* @return string $action имя контроллера
	*/
	public static function getAction(){
		return self::$_route->getAction();
	}

	/**
	* getParam
	*
	* Возвращяет параметры на основе фильтра
	* 
	* @return array
	*/
	public static function getParam($format = false){
		return self::$_route->getParam($format);
	}

	private static $DBprovider = array();

	/**
	* DB
	*
	* @param string $provider ключ БД настроек из файла config.php
	* 
	* @return Gallant\DB\DBProvider(Mysql/../..)
	*/
	public static function DB($provider){
		if(!isset(self::$DBprovider[$provider])){
			//p('search init');
			$config = G::getConfig('db');
			if(!$config[$provider]){
				throw new \Gallant\Exceptions\CoreException('error db config: '.$provider);
			}
			$prov = '\Gallant\DB\DBProvider'.ucfirst($config[$provider]['provider']);
			if(!class_exists($prov)){
				throw new \Gallant\Exceptions\CoreException('We do not yet support this database: '.$provider);
			}
			self::$DBprovider[$provider] = new $prov($config[$provider]);
		}
		return self::$DBprovider[$provider];
	}

	private static $error = array();

	/**
	* getError
	*
	* @param string $key ключ ошибки
	* 
	* @return mixed
	*/
	public static function getError($key){
		if(isset(self::$error[$key])) return self::$error[$key];
		else return false;
	}

	/**
	* setError
	*
	* @param string $key ключ ошибки
	* @param string $val доп. данные об ошибке, вернет метод getError
	* 
	* @return mixed
	*/
	public static function setError($key, $val = true){
		self::$error[$key] = $val;
	}

	/**
	* includeComponent
	*
	* @param string $path путь к файлам от config.php path=>include
	*/
	public static function includeComponent($path){
		$path_core = GALLANT_CORE.'/include/';
		$path_site = self::getPath('include').'/';
		if(is_file($path_core.$path)){
			include_once $path_core.$path;
			return;
		}else if(is_file($path_site.$path)){
			include_once $path_site.$path;
			return;
		}else{
			throw new \Gallant\Exceptions\CoreException('Error includeComponent (not file): '.$path);
		}
	}

	/**
	* ref
	*
	* @param string $url адрес редиректа
	*/
	public static function ref($url = false){
	    if(!$url) $url = $_SERVER['REQUEST_URI'];
	    $url = self::template()->link($url);
	    ob_clean();
	    header('Location: '.$url);
	    exit;
	}

	/**
	* isAjax
	* 
	* @return boolean
	*/
	public static function isAjax(){
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] =='XMLHttpRequest'){
            $type = explode(',',$_SERVER['HTTP_ACCEPT']);
            if(in_array('application/json', $type)) return true;
            else return false;
        }else return false;
    }
}