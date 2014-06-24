<?
/**
* G
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

use \Gallant\DB\SqlQuery;
use \Gallant\GConst;
use \Gallant\Exceptions\CoreException;
use \Gallant\Components\Template;
use \Gallant\Components\Route;

class G{
	private function __construct(){}
	private function __clone(){}

	private static $config = array();

	private static $request = array();

	private static $control = false;
	private static $action = false;

	private static $template = false;
	private static $_route = false;

	private static $system_path = false;

	private static $_domen = false;

	public static $filter = array();

	private static $registr = array();

	private static $DBprovider = array();

	private static $error = array();

	private static $cookie;
	
	/**
	* G::version
	* 
	* @return string version
	*/
	public static function version(){
		return "0.1.2 alfa";
	}
	
	/**
	* G::init
	* 
	* @param string $config_file путь к файлу конфига
	*/
	public static function init($config_file){
		session_start();

		self::$_domen = $_SERVER['SERVER_NAME'];

		self::$cookie = $_COOKIE;

		self::$filter['html'] = function(&$v,$k,$filter){
			if(!is_array($v)){
				$v = htmlspecialchars($v);
			}else array_walk($v, $filter, $filter);
		};

		self::$request = array('post' => $_POST, 'get' => $_GET, 'files' => $_FILES);

		$def_config = include GALLANT_CORE.GConst::DEFAULT_CONFIG;
		if(!is_array($def_config)){
			$_path = GConst::DEFAULT_CONFIG;
			throw new CoreException('not default config file '.$_path, 10);
		}
		if(!is_file($config_file)){
			throw new CoreException('not config file', 11);
		}
		$app_config = include $config_file;
		if(!is_array($app_config)){
			throw new CoreException('error data config file', 12);
		}
		
		self::$config = array_replace_recursive($def_config, $app_config);
		
		self::$template = new Template();

		self::$_route = new Route();

		G::includeComponent('array_column.php');
		
		$entry = false;
		if(class_exists('Entry')){
			Entry::init();
			$entry = true;
		}

		$control = self::getControl();
		$action = self::getAction();

		self::template()->ob();

		$apply = new $control();
		$result = $apply->$action();

		self::template()->ob();

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
	* getDomen
	* 
	* @return string domen name
	*/
	public static function getDomain(){
		return self::$_domen;
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
				'%site%' => FOLDER_SITE,
				'%root%' => FOLDER_ROOT,
				);
			$not_array = array('site', 'entry');
			foreach($config_path as $key => $value) {
				if(in_array($key, $not_array) && is_array($value)){
					throw new CoreException('Config (path=>'.$key.'): can not be an array');
				}
				$path[$key] = str_replace(array_keys($path_param), array_values($path_param), $value);
			}
			$f = function(&$v, $k, $f){
				if(is_array($v)){
					array_walk($v, $f, $f);
				}else{
					if($v[strlen($v) - 1] != DIRECTORY_SEPARATOR && substr($v, -4) != '.php') $v = $v.DIRECTORY_SEPARATOR;
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
	public static function getRequest($type, $key = false, $filter = 'html'){
		$type = strtolower($type);
		if($type != 'post' && $type != 'get' && $type != 'files'){
			throw new CoreException('error type Request in function G::getRequest($type, $key, $filter)');
		}
		$param = false;
		if(!$key){
			$param = self::$request[$type];
		}else if(isset(self::$request[$type][$key])){
			if(!self::$request[$type][$key]){
				$param = true;
			}else{
				$param = self::$request[$type][$key];
			}
		}

		if($param && is_array($param) && $filter && isset(self::$filter[$filter])){
			array_walk($param, self::$filter[$filter], self::$filter[$filter]);
		}
		return $param;
	}

	/**
	* getSession
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
		if(!isset($_SESSION['gallant_system']['close_session'][$key])){
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
	* setCookie
	*
	* Установка Cookie
	* 
	* @param string $key имя Cookie
	* @param string $val значение Cookie
	* @param int $time время жизни Cookie
	*/
	public static function setCookie($key, $val, $time = null){
		if(is_null($time)){
			$time = 1411200;
		}
		return setcookie($key, $val, time() + $time, '/', false, false, false);
	}

	/**
	* getCookie
	*
	* Установка Cookie
	* 
	* @param string $key имя Cookie
	*/
	public static function getCookie($key){
		if(isset(self::$cookie[$key])){
			return self::$cookie[$key];
		}
		return false;
	}

	/**
	* isCookie
	*
	* Проверка наличия Cookie
	* 
	* @param string $key имя Cookie
	*/
	public static function isCookie($key){
		if(isset(self::$cookie[$key])){
			return true;
		}
		return false;
	}

	/**
	* removeCookie
	*
	* Удаление Cookie
	* 
	* @param string $key имя Cookie
	*/
	public static function removeCookie($key){
		unset(self::$cookie[$key]);
		return setcookie($key, '', time() - 3600);
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

	/**
	* DB
	*
	* @param string $provider ключ БД настроек из файла config.php
	* 
	* @return Gallant\DB\DBProvider(Mysql/../..)
	*/
	public static function DB($provider){
		if(!isset(self::$DBprovider[$provider])){
			$config = G::getConfig('db');
			if(!$config[$provider]){
				throw new CoreException('error db config: '.$provider);
			}
			$prov = '\Gallant\DB\DBProvider'.ucfirst($config[$provider]['provider']);
			if(!class_exists($prov)){
				throw new CoreException('We do not yet support this database: '.$provider);
			}
			self::$DBprovider[$provider] = new $prov($config[$provider]);
		}
		return self::$DBprovider[$provider];
	}

	/**
	* dbQuery
	*
	* @param string $provider ключ БД настроек из файла config.php
	* 
	* @return new \Gallant\DB\DBQuery($provider);
	*/
	public static function dbQuery($provider = false){
		return new SqlQuery($provider);
	}

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
	* getErrorKeys
	*
	* 
	* @return array - массив всех зарегестрированных ошибок
	*/
	public static function getErrorKeys(){
		return array_keys(self::$error);
	}


	/**
	* includeComponent
	*
	* @param string $path путь к файлам от config.php path=>include
	*/
	public static function includeComponent($path){
		$path_core = GALLANT_CORE . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR;
		// include core component
		if(is_file($path_core.$path)){
			include_once $path_core.$path;
			return;
		}
		
		$path_sites = self::getPath('include');
		if(!is_array($path_sites)){
			$path_sites = array($path_sites);
		}
		// include site component
		foreach ($path_sites as $path_site) {
			if(is_file($path_site . $path)){
				include_once $path_site.$path;
				return;
			}
		}
		throw new CoreException('Error includeComponent (not file): '.$path);
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

	/**
	* setFlag - install flash messages
	* 
	* @param string - name flash messages
	* @param mixed (default true)
	*/
	public static function setFlag($name_flag, $val_flag = true){
		$_SESSION['gallant_system']['flash_messages'][$name_flag] = $val_flag;
	}

	/**
	* getFlag - return flash messages
	*
	* @param string - name flash messages
	* @return mixed (value flash messages)
	*/
	public static function getFlag($name){
		if(isset($_SESSION['gallant_system']['flash_messages'][$name])){
			$val = $_SESSION['gallant_system']['flash_messages'][$name];
			unset($_SESSION['gallant_system']['flash_messages'][$name]);
			return $val;
		}
		return false;
	}

	/**
	* getFlagKeys - return keys flash messages
	*/
	public static function getFlagKeys(){
		if(isset($_SESSION['gallant_system']['flash_messages'])){
			return array_keys($_SESSION['gallant_system']['flash_messages']);
		}
		return array();
	}
}
