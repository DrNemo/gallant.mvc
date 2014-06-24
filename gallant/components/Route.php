<?
/**
* Gallant\Components\Route
* Компонент Route, определяет какой control и action будет вызван. А так же дополнительные параметры вызова
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Components;
use \G;
use \Gallant\Exceptions\CoreException;

class Route{
	private $control = false;
	private $action = false;

	private $default_control = false;
	private $default_action = false;

	private $error_404 = false;

	private $param = array();

	private $routes = array();

	private $urls = array(); // url active control

	const PREF_CONTROL = 'control';
	const PREF_ACTION = 'action';
	const PREF_AJAX = 'ajax';
	const PREF_NAMESPACE = '\\Control\\';

	function __construct(){
		$config_route = G::getConfig('route');
		
		list($control, $action) = explode('/', $config_route['index']);
		$this->default_control = $control;
		$this->default_action = $action;
		$this->error_404 = $config_route['error404'];

		if($config_route['type'] == 'get'){
			$route = G::getRequest('get', 'route');
		}else if($config_route['type'] == 'request'){
			$route = parse_url($_SERVER['REQUEST_URI']);
			$route = $route['path'];
		}else{
			throw new CoreException('error type route');
		}
		$this->setRoutes($route);
	}

	private function route(){
		$action_pref = G::isAjax() ? self::PREF_AJAX : self::PREF_ACTION;

		$routes = $this->routes;
		$this->urls = array();
		$param = array();
		
		$namespace = self::PREF_NAMESPACE;
		$pod_folder = DIRECTORY_SEPARATOR;
		$control = false;
		$action = false;
		$param = array();

		$control_folder = G::getPath('control');
		if(!$control_folder){
			throw new CoreException('error config path control');
		}
		if(!is_array($control_folder)){
			$control_folder = array($control_folder);
		}

		while(!$control || !$action){
			if($routes){
				$rout = array_shift($routes);
				$routU = ucfirst($rout);
			}

			if(!$control){ // search control
				if(!isset($rout)){
					$rout = $this->default_control;
					$routU = ucfirst($this->default_control);
				}
				if(class_exists($namespace . self::PREF_CONTROL . $routU)){
					$control = $namespace . self::PREF_CONTROL . $routU;
					$this->urls[] = $rout;
					unset($rout, $routU);
					continue;
				}else{
					$search_folder = false;
					foreach($control_folder as $folder){
						if(is_dir($folder . $pod_folder . $rout)){
							$namespace .= $routU . '\\';
							$pod_folder .= $rout . DIRECTORY_SEPARATOR;
							$search_folder = true;
							$this->urls[] = $rout;
							unset($rout, $routU);
							break;
						}
					}
					if(!$search_folder){
						$def_contr = ucfirst($this->default_control);
						if(class_exists($namespace . self::PREF_CONTROL . $def_contr)){
							$control = $namespace . self::PREF_CONTROL . $def_contr;
							$this->urls[] = $this->default_control;
							if(method_exists($control, $action_pref . $routU)){
								$action = $action_pref.$routU;
								$this->urls[] = $rout;
								unset($rout, $routU);
							}else{
								list($control, $action) = $this->error404($control);
							}
						}else{
							list($control, $action) = $this->error404($control);
						}
					}
				}
			}
			if($control && !$action){ // search action
				if(!isset($rout)){
					$rout = $this->default_action;
					$routU = ucfirst($this->default_action);
				}
				if(method_exists($control, $action_pref . $routU)){					
					$action = $action_pref.$routU;
					$this->urls[] = $rout;
					unset($rout, $routU);
				}else if(method_exists($control, $action_pref . $this->default_action)){
					$action = $action_pref . $this->default_action;
					$this->urls[] = $this->default_action;
				}else{
					list($control, $action) = $this->error404($control);
				}
			}
		}

		$this->control = $control;
		$this->action = $action;

		if($routes){
			$param = array_merge($param, $routes);
		}
		$this->param = $param;
	}

	private function error404($control){
		header("HTTP/1.0 404 Not Found");
		if(method_exists($control, $this->error_404)){
			$this->urls[] = $this->error_404;
			return array($control, $this->error_404);
		}else if(method_exists(self::PREF_NAMESPACE . self::PREF_CONTROL . $this->default_control, $this->error_404)){
			$this->urls = array($this->default_control, $this->error_404);
			return array(self::PREF_NAMESPACE . self::PREF_CONTROL . $this->default_control, $this->error_404);
		}else{
			throw new CoreException("Not found 404 method, use $control extends \Gallant\Prototype\controlDefault");
		}
	}

	/**
	* getRoutes возвращяет адрес вызванного контроллера 
	* 
	* @return array 
	*/
	function getRoutes(){
		return $this->routes;
	}

	/**
	* setRoutes позволяет изменить адрес контроллера, однако вызван должен быть до Entry::render
	* 
	* @param string 
	*/
	function setRoutes($routes){
		$routes = array_values(array_filter(explode('/', strtolower($routes).'/'), 'trim'));
		array_walk($routes, G::$filter['html'], G::$filter['html']);
		$this->routes = $routes;
		$this->route();
	}

	/**
	* getUrl вернет корректный url для текущего контроллера в виде массива
	* 
	* @return array 
	*/
	function getUrl(){
		return $this->urls;
	}

	/**
	* getUrlStr вернет корректный url для текущего контроллера в виде строки адреса
	* 
	* @return string 
	*/
	function getUrlStr(){
		return '/'.strtolower(implode('/', $this->urls)).'/';
	}

	/**
	* setControl позволяет изменить выбранный контроллер, однако вызван должен быть до Entry::render
	* 
	* @param string - class name (Control\Test\controlPage) 
	*/
	function setControl($control){
		if(!class_exists('\\'.$control)){
			throw new CoreException('error not control :'.$control);
		}
		$this->control = $control;
	}

	/**
	* getControl вернет имя выбраного контроллера
	* 
	* @return string - control name (Control\Test\controlPage) 
	*/
	function getControl(){
		return $this->control;
	}

	/**
	* setAction позволяет изменить выбранный action, однако вызван должен быть до Entry::render
	* 
	* @param string - action name (actionMyIndex) 
	*/
	function setAction($action){
		if(!method_exists('\\'.$this->control, $action)){
			throw new CoreException('error not method "'.$action.'" in control :'.$this->control);
		}
		$this->action = $action;
	}

	/**
	* getAction вернет имя выбраного action
	* 
	* @return string - action name (actionMyIndex) 
	*/
	function getAction(){
		return $this->action;
	}

	/**
	* getParam вернет дополнительные параметры роутинга
	* 
	* @param $format mixed
	* @return array - action name (actionMyIndex) 
	*/
	function getParam($format = false){
		if(is_array($format)){
			$format_count = sizeof($format);
			$param_count = sizeof($this->param);
			if($format_count < $param_count){
				$new_param = array_slice($this->param, 0, $format_count);
			}else if($format_count > $param_count){
				$new_param = array_pad($this->param, $format_count, false);
			}else{
				$new_param = $this->param;
			}
			$param = array_combine($format, $new_param);
			return $param;
		}else if($format){
			$params = array_chunk($this->param, 2);
			$return_params = array();
			foreach($params as $param){
				$return_params[$param[0]] = (isset($param[1]))?$param[1]:true;
			}
			return $return_params;
		}else{
			return $this->param;
		}
	}
}