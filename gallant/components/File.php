<?
/**
* Gallant\Components\File
* 
* @package Gallant
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Components;
use \G as G;

class File{
	public $folder = false;
	public $file = false;
	public $ext = false;
	public $path = false;

	function __construct($file){
		if(is_file($file)){
			$pars = pathinfo($file);
			$this->folder = $pars['dirname'];
			$this->file = $pars['basename'];
			$this->ext = $pars['extension'];
			$this->path = $file;
		}
	}

	static function load($load, $new_file){

		$ext = basename($load['name']);
		$new_file = str_replace('%ext%', $ext, $new_file);
		self::dir($new_file);

		if ($load["error"] == UPLOAD_ERR_OK && is_uploaded_file($load['tmp_name']) && $new_file) {
			if(move_uploaded_file($load['tmp_name'], $new_file)){
				return new File($new_file);
			}
		}
		return false;
	}

	static function dir($file){
		$pars = pathinfo($file);
		if(!is_dir($pars['dirname'])){
			$dirs = explode('/', $pars['dirname']);
			$re_dir = '';
			while ($dirs) {
				$d = array_shift($dirs);
				$re_dir .= $d.'/';
				if(!is_dir($re_dir)){
					mkdir($re_dir, 0755);
				}
			}
		}
	}

	function copy($new_file, $folder = true){
		if(!$this->file) return false;
		$fold = ($folder) ? $new_file : $this->folder.'/'.$new_file;
		self::dir($fold);

		if(copy($this->path, $fold)){
			return new File($fold);
		}
		return false;
	}

	function delete(){

	}

	function size(){

	}

	function info(){

	}
}