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

	private $_routes = array();

	private $_urls = array();
	private $_path = array();

	const PREF_CONTROL = 'control';
	const PREF_ACTION = 'action';
	const PREF_AJAX = 'ajax';

	function __construct(){
		$config_route = G::getConfig('route');
		
		list($control, $action) = explode('/', $config_route['index']);
		$this->default_control = ucfirst($control);
		$this->default_action = ucfirst($action);
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

	function route(){
		$default_control = self::PREF_CONTROL.$this->default_control;
		$default_action = self::PREF_ACTION.$this->default_action;

		$action_404 = $this->error_404;
		$folder_control = G::getPath('control');

		$routes = $this->_routes;
		$routes = array_filter($routes);

		array_walk($routes, G::$filter['html'], G::$filter['html']);
		
		if(!$folder_control){
			throw new CoreException('error path control in config');
		}

		$_control = '\\Control\\';
		$_action = G::isAjax() ? self::PREF_AJAX : self::PREF_ACTION;

		$_control_flag = $_action_flag = false;

		$param = array();
		while(!$_control_flag || !$_action_flag){

			$rout = false;
			if($routes){
				$rout = array_shift($routes);
				$routU = ucfirst($rout);
			}
			if($rout){
				if(!$_control_flag){
					// проверка наличия папки
					if(is_dir($folder_control.$rout) && !$_control_flag){
						$_control .= $rout.'\\';
						$folder_control .= $rout.'/';

						$this->_urls[] = $rout;
						$this->_path[] = $routU;
					// наличее контроллера в "папке"
					}else if(class_exists($_control.self::PREF_CONTROL.$routU)){
						$_control .= self::PREF_CONTROL.$routU;
						$_control_flag = true;

						$this->_urls[] = $rout;
						$this->_path[] = $routU;
					// если контроллер не найден
					}else{
						/* ищем default control в текущей папке */
						if(class_exists($_control.$default_control)){
							$_control .= $default_control;
							$_control_flag = true;

							$this->_path[] = $this->default_control;
							/* ищем метод в default control */
							if(method_exists($_control, $_action.$routU)){
								$_action .= $routU;
								$_action_flag = true;

								$this->_path[] = $routU;
								$this->_urls[] = $rout;
							/* иначе ищем метод 404*/
							}else if(method_exists($_control, $action_404)){
								$_action = $action_404;
								$_action_flag = true;
								$this->_path[] = '404';
								$param[] = $rout;
							/* вызываем корневой default control и его метод 404 */
							}else{
								$_control = '\\Control\\'.$default_control;
								$_action = $action_404;
								$_control_flag = $_action_flag = true;
								
								$this->_urls = array($this->default_control, '404');
								$this->_path = array($this->default_control, '404');

								$param[] = $rout;
							}
						}else if(method_exists('\\Control\\'.$default_control, $action_404)){
							$_control = '\\Control\\'.$default_control;
							$_action = $action_404;
							$_control_flag = $_action_flag = true;

							$this->_urls = array($this->default_control, '404');
							$this->_path = array($this->default_control, '404');

						}else{
							throw new CoreException('error not method 404 in default control');
						}
					}
				}else if(!$_action_flag){
					if(method_exists($_control, $_action.$routU)){                        
						$_action .= $routU;
						$_action_flag = true;

						$this->_urls[] = $rout;
						$this->_path[] = $rout;

					}else if(method_exists($_control, $action_404)){
						$_action = $action_404;
						$_action_flag = true;

						$this->_urls[] = '404';
						$this->_path[] = '404';

					}else if(method_exists('\\Control\\'.$default_control, $action_404)){
						$_control = '\\Control\\'.$default_control;
						$_action = $action_404;
						$_action_flag = $_control_flag = true;

						$this->_urls = array($this->default_control, '404');
						$this->_path = array($this->default_control, '404');
					}else{
						throw new CoreException('error: not method 404 in default control');
					}
				}
			}else{
				// если нет контроллера
				if(!$_control_flag){
					if(class_exists($_control.$default_control)){
						$_control .= $default_control;
						$_control_flag = true;
						$this->_path[] = $this->default_control;
					}else if(class_exists('\\Control\\'.$default_control)){
						$_control = '\\Control\\'.$default_control;
						$_control_flag = true;
						$this->_path[] = $this->default_control;
						if(method_exists($_control, $action_404)){
							$_action = $action_404;
							$this->_path[] = '404';
							$_action_flag = true;
						}else{
							throw new CoreException('error not method 404 in default control');
						}
					}
				}
				if(!$_action_flag){
					if(method_exists($_control, $default_action)){
						$_action = $default_action;
						$_action_flag = true;						
						$this->_path[] = $this->default_action;
					}else if(method_exists($_control, $action_404)){
						$_action = $action_404;
						$_action_flag = true;
						$this->_path[] = '404';
					}else if(method_exists('\\Control\\'.$default_control, $action_404)){
						$_control = '\\Control\\'.$default_control;
						$_action = $action_404;
						$_control_flag = $_action_flag = true;
						$this->_path = array($this->default_control, '404');
					}else{
						throw new CoreException('error not method 404 in default control');
					}
				}
			}
		}
		
		if($routes){
			$param = array_merge($param, $routes);
		}

		$this->control = $_control;
		$this->action = $_action; 
		$this->param = $param;
	}

	private function errorPage($code){
		/**
		*@todo
		*/
	}

	/**
	* getRoutes возвращяет адрес вызванного контроллера 
	* 
	* @return array 
	*/
	function getRoutes(){
		return $this->_routes;
	}

	/**
	* setRoutes позволяет изменить адрес контроллера, однако вызван должен быть до Entry::render
	* 
	* @param string 
	*/
	function setRoutes($routes){
		$this->_routes = array_values(array_filter(explode('/', strtolower($routes).'/'), 'trim'));
		$this->route();
	}

	/**
	* getUrl вернет корректный url для текущего контроллера в виде массива
	* 
	* @return array 
	*/
	function getUrl(){
		return $this->_urls;
	}

	/**
	* getUrlStr вернет корректный url для текущего контроллера в виде строки адреса
	* 
	* @return string 
	*/
	function getUrlStr(){
		return '/'.strtolower(implode('/', $this->_urls)).'/';
	}

	/**
	* getUrl вернет путь к текущемму контроллеру в виде массива
	* 
	* @return array 
	*/
	function getPath(){
		return $this->_path;
	}

	/**
	* getPathStr вернет корректный url для текущего контроллера в виде строки адреса
	* 
	* @return string 
	*/
	function getPathStr(){
		return '/'.strtolower(implode('/', $this->_path)).'/';
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