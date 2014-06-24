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
use \Gallant\Exceptions\CoreException;

class File{
	public $folder = false;
	public $file = false;
	public $ext = false;
	public $path = false;
	public $src = false;
	public $chmod = false;

	function __construct($file, $chmod = '0755'){
		if(is_file($file)){
			$this->chmod = fileperms($file);
			$pars = pathinfo($file);
			$this->folder = $pars['dirname'].'/';
			$this->file = $pars['basename'];
			$this->ext = $pars['extension'];
			$this->path = $file;
			$this->src = substr($this->path, strlen(FOLDER_ROOT));
		}else{
			throw new CoreException("Not found file: $file");
		}
	}

	static function load($load, $new_file){
		$ext = pathinfo($load['name'], PATHINFO_EXTENSION);		
		$new_file = str_replace('%ext%', $ext, $new_file);
		self::dir($new_file);

		if ($load["error"] == UPLOAD_ERR_OK && is_uploaded_file($load['tmp_name']) && $new_file) {
			if(move_uploaded_file($load['tmp_name'], $new_file)){
				return new File($new_file);
			}else{
				return false;
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
					mkdir($re_dir, 0777);
				}
			}
		}
	}

	static function readfile($folder){
		if(!is_dir($folder)){
			throw new CoreException("Not found folder: $folder");
		}
		$files = array();
		$_f = scandir($folder);
		$end_str = $folder[strlen($folder) - 1];
		if($end_str == '/' && $end_str = '\\'){
			$folder = substr($folder, 0, strlen($folder) - 1) . DIRECTORY_SEPARATOR;
		}else{
			$folder .= DIRECTORY_SEPARATOR;
		}

		if($_f) foreach ($_f as $file) {
			if(is_file($folder.'/'.$file)){
				$files[] = new File($folder.$file);
			}
		}
		return $files;
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
		return unlink($this->path);
	}

	function size(){
		return filesize($this->path);
	}

	function info(){

	}

	private $image_info = false;
	function image(){
		if(!$this->image_info){
			$size = getimagesize($this->path);
			$this->image_info = array(
				'width' => $size[0],
				'height' => $size[1],
				'mime' => $size['mime']
			);
		}
		
		return (object)$this->image_info;
	}
}