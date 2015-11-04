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
	protected $control = false;
	protected $action = false;

	protected $default_control = false;
	protected $default_action = false;

	protected $error_404 = false;

	protected $param = [];

	protected $routes = [];

	protected $urls = []; // url active controller

    const BASE_NAMESPACE = 'Controller';
	const PREF_CONTROL = 'Controller';
	const PREF_ACTION = 'action';
	const PREF_AJAX = 'ajax';

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
		$this->urls = [];

		$control_folder = G::getPath('controller');
		if(!is_array($control_folder)){
			$control_folder = [$control_folder];
		}

		foreach($control_folder as $folder) {
			$this->findController($folder);
			if($this->control && $this->findAction()){
				break;
			}
		}

		if(!$this->control){
			$this->control = static::BASE_NAMESPACE . '\\' . $this->getControllerName($this->default_control);
			$this->findAction(false);
		}

		$this->param = $this->routes;

		if(!$this->action){
			// @TODO Logger 404
			$this->findActionByName($this->error_404);

			if(!$this->action){
				throw new CoreException('Not found 404 action');
			}
		}
	}

	protected function getControllerName($name){
		return static::PREF_CONTROL . ucfirst($name);
	}

	protected function getActionName($name){
		return (G::isAjax() ? static::PREF_AJAX : static::PREF_ACTION) . ucfirst($name);
	}

	protected function findController($folder){
		if(!sizeof($this->routes)){
			return;
		}
		$url = [];

		$namespace = '\\';
		$namespace_path = '';

		while(sizeof($this->routes)){
			$rout = array_shift($this->routes);
			$url[] = $rout;

			if(is_dir($folder . $namespace_path . $rout)){
				$namespace_path .= $rout . DIRECTORY_SEPARATOR;
				$namespace .= ucfirst($rout) . '\\';
				continue;
			}

			$className = static::BASE_NAMESPACE . $namespace . $this->getControllerName($rout);
			if(class_exists($className)){
				$this->control = $className;
				$this->urls = $url;
			}

			break;
		}

		if(!$this->control){
			$this->routes = array_merge($url, $this->routes);
		}
	}

	protected function findAction($findDefault = true){
		if(sizeof($this->routes)){
			if($this->findActionByName($this->routes[0])){
				unset($this->routes[0]);
				return true;
			}
		}
		if(!$findDefault){
			return false;
		}

		if($this->findActionByName($this->default_action)){
			return true;
		}
		return false;
	}

	protected function findActionByName($actionName){
		$actionMethod = $this->getActionName($actionName);
		if(is_callable([$this->control, $actionMethod])){
			$this->action = $actionMethod;
			$this->urls[] = $actionName;
			return true;
		}
		return false;
	}

	protected $request;
	public function getRequest(){
		if(empty($this->request)){
			$this->request = G::getComponent(Request::class);
		}
		$this->route();

		$this->request->setRunAction($this->control, $this->action);
		$this->request->setArgs($this->param);

		return $this->request;
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