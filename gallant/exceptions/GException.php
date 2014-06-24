<?
/**
* Gallant\Exceptions\GException
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Exceptions;
class GException extends \Exception{
	protected $code = NULL;
	protected $message = NULL;
	protected $file = NULL;
	protected $line = NULL;
	protected $stack = array();

	function __construct($message = NULL, $code = 0) {
		$this->stack = debug_backtrace(false);
		if($this->stack[0] && $this->stack[0]['class'] == __CLASS__){
			$this->file = $this->stack[0]['file'];
			$this->line = $this->stack[0]['line'];
			unset($this->stack[0]);
		}
		$this->message = $message;
		$this->code = $code;

		$this->display();
	}

	private function display(){
		//ob_clean();
		echo $this->css();
		echo "<div class=\"GallantExceptions\">
			<div class=\"GallantExceptionsTitle\">Gallant\Exception\GException:<br> <b>$this->message</b> [ error code <b>$this->code</b> ]</div>
			<div class=\"GallantExceptionsStack\"> <b>Function Stack:</b>";

		if($this->stack) foreach ($this->stack as $data) {
			if(!isset($data['file'])) $data['file'] = 'N/A';
			if(!isset($data['line'])) $data['line'] = 'N/A';
			if(!isset($data['class'])) $data['class'] = 'N/A';
			if(!isset($data['type'])) $data['type'] = 'N/A';
			if(!isset($data['function'])) $data['function'] = 'N/A';

			echo "<div class=\"GallantExceptionsStekItem\"><b>File:</b> $data[file]:$data[line], <b>Function:</b> $data[class] $data[type] $data[function], <b>Args:</b>";
			echo "<pre>";
			print_r($data['args']);
			echo "</pre></div>";
		}
		echo "</div></div>";
		exit;
	}

	private function css(){
		return "<style>
		.GallantExceptions{
			border-radius: 4px;
			border: 1px solid #f00;
			padding: 15px;
			color: #a94442;
			background-color: #f2dede;
			border-color: #ebccd1;
			font-size: 14px;
		}
		.GallantExceptionsStack{
			margin-top: 10px;
		}
		.GallantExceptionsTitle{
			font-size: 20px;
		}
		.GallantExceptionsStekItem{
			border-bottom: 1px solid #000;
			margin-bottom: 20px;
			padding-bottom: 5px;
		}
		.GallantExceptionsStekItem pre{
			font-size: 12px;
			padding: 0 15px;
			margin: 0;
		}
		</style>";
	}
}