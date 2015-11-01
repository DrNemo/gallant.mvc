<?
/**
* Gallant\Components\Route
* Компонент Route, определяет какой controller и action будет вызван. А так же дополнительные параметры вызова
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

	private $urls = array(); // url active controller

    const BASE_NAMESPACE = 'Controller';
	const PREF_CONTROL = 'Controller';
	const PREF_ACTION = 'action';

	function __construct(){
		$config_route = G::getConfig('route');
		
		list($control, $action) = explode('/', $config_route['index']);
		$this->default_control = $control;
		$this->default_action = $action;
		$this->error_404 = $config_route['error404'];

		if($config_route['type'] == 'get'){
			$route = urldecode(G::getRequest('get', '_r'));
		}else if($config_route['type'] == 'request'){
			$route = parse_url(urldecode($_SERVER['REQUEST_URI']));
			$route = $route['path'];
		}else{
			throw new CoreException('error type route');
		}
		$this->setRoutes($route);
	}

	protected function route(){
		$routes = $this->routes;
		$this->urls = [];

		$control_folder = G::getPath('controller');
		if(!is_array($control_folder)){
			$control_folder = [$control_folder];
		}

        $control = false;
        $action = false;

		foreach($control_folder as $folder) {
			$find = $this->findController($routes, $folder);
			if($find){
				$control = $find[0];
				$action = $find[1];
				break;
			}
		}

        if(!$control){
            $control = static::BASE_NAMESPACE . '\\' . $this->getControllerName($this->default_control);
            $action = $this->getActionName($this->default_action);
        }
        if(sizeof($routes)){
            $this->param = $routes;
        }

        $this->control = $control;
        $this->action = $action;

	}

	protected function getControllerName($name){
		return static::PREF_CONTROL . ucfirst($name);
	}

	protected function getActionName($name){
		return static::PREF_ACTION . ucfirst($name);
	}

	protected function findController(array $routes, $folder){
		$control = false;
		$action = false;

		$namespace = '\\';
		$namespace_path = '';

		while(sizeof($routes)){
			$rout = array_shift($routes);
			$routU = ucfirst($rout);

			if(is_dir($folder . $namespace_path . $rout)){
				$namespace_path .= $rout . DIRECTORY_SEPARATOR;
				$namespace .= $routU . '\\';
				continue;
			}

			$className = static::BASE_NAMESPACE . $namespace . $this->getControllerName($rout);
			if(class_exists($className)){
				$control = $className;
				continue;
			}

			if(!$control){
				$control = static::BASE_NAMESPACE . '\\' . $this->getControllerName($this->default_control);
			}

			if($control){
				if(method_exists($control, static::PREF_ACTION . $routU)){
					$action = static::PREF_ACTION . $routU;
				}

				if(!$action && method_exists($control, static::PREF_ACTION . $this->default_action)){
					$action = static::PREF_ACTION . ucfirst($this->default_action);
				}

				if(!$action && method_exists($control, static::PREF_ACTION . $this->error_404)){
					$action = static::PREF_ACTION . $this->error_404;
				}

				if(!$action){
					$control = static::PREF_CONTROL . ucfirst($this->default_action);
					$action = static::PREF_ACTION . ucfirst($this->error_404);
				}
				break;
			}
		}

		return ($control && $action) ? [$control, $action] : false;
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
			throw new CoreException('error not controller :'.$control);
		}
		$this->control = $control;
	}

	/**
	* getControl вернет имя выбраного контроллера
	* 
	* @return string - controller name (Control\Test\controlPage)
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
			throw new CoreException('error not method "'.$action.'" in controller :'.$this->control);
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