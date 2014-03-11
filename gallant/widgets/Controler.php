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
use \Gallant\Exceptions\CoreException;

class Controler{
	protected $config;

	protected $value;

	protected $template;

	protected $widget;

	function __construct($name, $data){
		$this->value = $data;
		$this->widget = $name;
	}

	public function options($options){
		foreach ($options as $key => $value) {
			if(isset($this->config[$key])){
				$this->config[$key] = $value;
			}
		}
		return $this;
	}

	public function opt($key){
		if(isset($this->config[$key])){
			return $this->config[$key];
		}
		throw new CoreException("not found widget option : $key");
	}

	public function render(){
		if(!$this->template){
			$tpl = __DIR__ . DIRECTORY_SEPARATOR . $this->widget . DIRECTORY_SEPARATOR . 'template.php';
			$this->tpl($tpl);
		}
		ob_start();
		include $this->template;
		return ob_get_clean();
	}

	public function data(){
		return $this->value;
	}

	public function tpl($file){
		if(!is_file($file)){
			throw new CoreException("not found widget template: $file");
		}
		$this->template = $file;
	}
}