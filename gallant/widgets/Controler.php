<?
/**
* Gallant\Widget\Controler
*
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Widgets;

use \G;
use \Gallant\Exceptions\WidgetException;

class Controler{
	protected $config;

	protected $value;

	protected $template;

	protected $widget;

	final function __construct($name, $template = ''){
		$this->widget = $name;
		if($options){
			$this->setOptions($options);
		}
		$this->setTemplate($this->dir());
	}

	final public function setOptions($options){
		foreach ($options as $key => $value) {
			if(isset($this->config[$key])){
				$this->config[$key] = $value;
			}
		}
		return $this;
	}

	final public function getOption($key){
		if(isset($this->config[$key])){
			return $this->config[$key];
		}
		throw new WidgetException("not found widget option : $key");
	}

	final public function render($data = false, $file = false){
		if($file){
			$this->setTemplate($file);
		}
		if($data){
			$this->setData($data);
		}

		ob_start();
		include $this->template . DIRECTORY_SEPARATOR . 'template.php';
		return ob_get_clean();
	}

	final public function getData(){
		return $this->value;
	}

	final public function setData($data){
		$this->value = $data;
		return $this;
	}

	final public function setTemplate($tpl){
		$file = $tpl . DIRECTORY_SEPARATOR . 'template.php';
		if(!is_file($file)){
			throw new WidgetException("not found widget template: $tpl");
		}
		$this->template = $tpl;
	}

	public function dir(){
		throw new WidgetException('override function $this->dir()');
	}
}