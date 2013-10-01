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

namespace Gallant\Components;
use \G as G;

class Route{
	private $control = false;
	private $action = false;

    private $default_control = false;
    private $default_action = false;

    private $error_404 = false;

    private $param = array();

    private $_routes = false;

    private $_urls = array();

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
		}else if($config_route['type'] == 'request_url'){
			$route = parse_url($_SERVER['REQUEST_URI']);
            $route = $route['path'];
		}else{
			throw new \Gallant\Exceptions\CoreException('error type route');
		}
		$routes = array_values(array_filter(explode('/', strtolower($route).'/'), 'trim'));

        if(sizeof($routes) > 0){
            $this->_routes = $routes;
        }
	}

    function getUrl(){
        return $this->_routes;
    }

    function getStrUrl(){
        return '/'.strtolower(implode('/', $this->_urls));
    }

    function setUrl($url){
        $this->_routes = $url;
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
            throw new \Gallant\Exceptions\CoreException('error path control in config');
        }

        $_control = '\\Control\\';
        $_action = G::isAjax() ? 'ajax' : 'action';

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
                    // наличее контроллера в "папке"
                    }else if(class_exists($_control.self::PREF_CONTROL.$routU)){
                        $_control .= self::PREF_CONTROL.$routU;
                        $_control_flag = true;
                        $this->_urls[] = $rout;
                    // если контроллер не найден
                    }else{
                        /* ищем default control в текущей папке */
                        if(class_exists($_control.$default_control)){
                            $_control .= $default_control;
                            $_control_flag = true;
                            /* ищем метод в default control */
                            if(method_exists($_control, $_action.$routU)){
                                $_action .= $routU;
                                $_action_flag = true; 
                                $this->_urls[] = $this->default_control;
                                $this->_urls[] = $rout;
                            /* иначе ищем метод 404*/
                            }else if(method_exists($_control, $action_404)){
                                $_action = $action_404;
                                $_action_flag = true;
                                $this->_urls[] = $this->default_control;
                                $this->_urls[] = '404';
                                $param[] = $rout;
                            /* вызываем корневой default control и его метод 404 */
                            }else{
                                $_control = '\\Control\\'.$default_control;
                                $_action = $action_404;
                                $_control_flag = $_action_flag = true;
                                $this->_urls = array($this->default_control, '404');
                                $param[] = $rout;
                            }
                        }else if(method_exists('\\Control\\'.$default_control, $action_404)){
                            $_control = '\\Control\\'.$default_control;
                            $_action = $action_404;
                            $_control_flag = $_action_flag = true;
                            $this->_urls = array($this->default_control, '404');
                        }else{
                            throw new \Gallant\Exceptions\CoreException('error not method 404 in default control');
                        }
                    }
                }else if(!$_action_flag){
                    if(method_exists($_control, $_action.$routU)){
                        $_action .= $routU;
                        $_action_flag = true;
                        $this->_urls[] = $rout;
                    }else if(method_exists($_control, $action_404)){
                        $_action = $action_404;
                        $_action_flag = true;
                        $this->_urls[] = '404';
                    }else if(method_exists('\\Control\\'.$default_control, $action_404)){
                        $_control = '\\Control\\'.$default_control;
                        $_action = $action_404;
                        $_action_flag = $_control_flag = true;
                        $this->_urls = array($this->default_control, '404');
                    }else{
                        throw new \Gallant\Exceptions\CoreException('error not method 404 in default control');
                    }
                }
            }else{
                // если нет контроллера
                if(!$_control_flag){
                    if(class_exists($_control.$default_control)){
                        $_control .= $default_control;
                        $_control_flag = true;
                        $this->_urls[] = $this->default_control;
                    }else if(class_exists('\\Control\\'.$default_control)){
                        $_control = '\\Control\\'.$default_control;
                        $_control_flag = true;
                        $this->_urls[] = $this->default_control;
                        if(method_exists($_control, $action_404)){
                            $_action = $action_404;
                            $this->_urls[] = '404';
                            $_action_flag = true;
                        }else{
                            throw new \Gallant\Exceptions\CoreException('error not method 404 in default control');
                        }
                    }
                }
                if(!$_action_flag){
                    if(method_exists($_control, $default_action)){
                        $_action = $default_action;
                        $_action_flag = true;
                        $this->_urls[] = $this->default_action;
                    }else if(method_exists($_control, $action_404)){
                        $_action = $action_404;
                        $_action_flag = true;
                        $this->_urls[] = '404';
                    }else if(method_exists('\\Control\\'.$default_control, $action_404)){
                        $_control = '\\Control\\'.$default_control;
                        $_action = $action_404;
                        $_control_flag = $_action_flag = true;
                        $this->_urls = array($this->default_control, '404');
                    }else{
                        throw new \Gallant\Exceptions\CoreException('error not method 404 in default control');
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

    function setControl($control){
        if(!class_exists('\\'.$control)){
            throw new \Gallant\Exceptions\CoreException('error not control :'.$control);
        }
        $this->control = $control;
    }

    function getControl(){
        return $this->control;
    }

    function setAction($action){
        if(!method_exists('\\'.$this->control, $action)){
            throw new \Gallant\Exceptions\CoreException('error not method "'.$action.'" in control :'.$this->control);
        }
        $this->action = $action;
    }

    function getAction(){
        return $this->action;
    }

    function getParam($format){
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