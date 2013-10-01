<?
/**
* G
* 
* @package Gallant
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Components;
use \G as G;

class Template{
	private $skin = false;
	private $main = false;

	private $folder_template;
	private $folder_template_html;

	private $chaster = 'utf-8';

	public $folder;

	public $helper;

	function __construct(){
		$design = G::getConfig('site');
		$this->chaster = $design['chaster'];
		#$this->helper = new \Gallant\Helpers\HtmlHelper;

		header("Content-Encoding: ".$this->chaster);
        header("Content-Type: text/html; charset=".$this->chaster);

        header("Content-language: ".G::lang()->getLang());
		
		$this->setSkin($design['skin']);
		$this->setMain($design['main']);		

		ob_start();
	}

	function html(){
		return $this->helper;
	}

	function setSkin($skin){
		$folders = G::getPath('template');
        if(!$folders){
        	throw new \Gallant\Exceptions\CoreException('error template not folder template');
        }
        if(is_array($folders)){
	        $templ = false;
	        foreach ($folders as $folder) {
	        	if(is_dir($folder.$skin)){
	        		$templ = $folder;
	        		break;
	        	}
	        }
	    }else{
	    	$templ = $folders;
	    }
        if(!$templ){
        	throw new \Gallant\Exceptions\CoreException('error template not folder template');
        }

        $this->folder_template = $templ;
        $this->folder_template_html = str_replace('\\', '/', substr($templ, strlen(SITE_ROOT)));

		if(!is_dir($this->folder_template.$skin)){
			throw new \Gallant\Exceptions\CoreException('error template skin folder: '.$skin);
		}

		$this->skin = $skin;

		$this->folder = array(
			'skin' => $this->folder_template_html.$this->skin.'/',
			'js' => $this->folder_template_html.$this->skin.'/js/',
			'images' => $this->folder_template_html.$this->skin.'/images/',
			'css' => $this->folder_template_html.$this->skin.'/style/',
			);		
	}

	function getSkin(){
		return $this->skin;
	}

	function setMain($main){
		$file = $this->folder_template.$this->skin.'/'.$main.'.php';
		if(!is_file($file)){
			throw new \Gallant\Exceptions\CoreException('error template main: '.$this->skin.'/'.$main.'.php');
		}
		$this->main = $main;
	}

	function getMain(){
		return $this->main;
	}

	private $content = '';
	function tpl($file, $Result = array()){
		$Result = (object)$Result;
		if(!is_file($this->folder_template.$this->skin.'/control/'.$file.'.php')){
			echo 'not file tpl: '.$file;
		}else{
			include $this->folder_template.$this->skin.'/control/'.$file.'.php';
		}
		$this->content .= ob_get_clean();
		ob_start();
	}

	function render($result = false){
		if(!G::isAjax()){
			$this->content .= ob_get_clean();
			ob_start();
			include $this->folder_template.$this->skin.'/'.$this->main.'.php';
			$content = ob_get_clean();

			if(!is_file($this->folder_template.$this->skin.'/head.php')){
				throw new \Gallant\Exceptions\CoreException('error template head in skin: '.$this->skin);
			}
			
			ob_start();
			include $this->folder_template.$this->skin.'/head.php';
			// compres
			$content = $this->htmlcompress(ob_get_clean());
			// gzip
			ob_start('ob_gzhandler');
			echo $content;
		}else{
			$this->content .= ob_get_clean();
			$return = array(
				'code' => 200,
				'result' => array(
					'data' => $result,
					'content' => $this->content
					)
				);
			echo json_encode($return);
		}
	}

	function layer($file, $Result = array()){
		$Result = (object)$Result;
		if(!is_file($this->folder_template.$this->skin.'/layer/'.$file.'.php')){
			echo '<div>not file layer: '.$file.'</div>';
		}else{
			include $this->folder_template.$this->skin.'/layer/'.$file.'.php';
		}
	}

	private $file_js = array();
	function setJs(){
		$files = func_get_args();
		$this->file_js = array_merge($this->file_js, $files);
	}

	function includeJs(){
		$files = func_get_args();
		$this->file_js = array_merge($files, $this->file_js);
		$include = '';
		foreach($this->file_js as $file){
			$parse = parse_url($file);
			$path = isset($parse['host']) ? $file : $this->folder['js'].$file;
			$include .= '<script type="text/javascript" src="'.$path.'"></script>';			
		}
		return $include;
	}

	private $file_css = array();
	function setCss(){
		$files = func_get_args();
		$this->file_css = array_merge($this->file_css, $files);
	}

	function includeCss(){
		$files = func_get_args();
		$this->file_css = array_merge($files, $this->file_css);
		$include = '';
		foreach($this->file_css as $file){
			$parse = parse_url($file);
			$path = isset($parse['host']) ? $file : $this->folder['css'].$file;
			$include .= '<link type="text/css" rel="stylesheet" href="'.$path.'">';			
		}
		return $include;
	}

	function srcImg($img){
		return $this->folder['images'].$img;
	}

	function link($path){
		$route = G::getConfig('route');
		if($route['type'] == 'get'){
			return '?route='.$path;
		}else{
			return $path;
		}
	}

	private $meta = array(
		'title' => '',
		'description' => '',
		'keywords' => ''
		);
	function getMeta($key){
		if(isset($this->meta[$key])){
			return $this->meta[$key];
		}
		return false;
	}

	function setMeta($key, $val = ''){
		if(is_array($key)){
			foreach ($key as $type => $value) {
				if(isset($this->meta[$type])){
					$this->meta[$type] = $value;
				}
			}
		}else if(isset($this->meta[$key])){
			$this->meta[$key] = $val;
		}else return false;
		return true;
	}

	function getFolder($type){
		if(isset($this->folder[$type])){
			return $this->folder[$type];
		}
		return false;
	}

	function includeGallantJs(){
		if(!is_file(FOLDER_ROOT.'/cache/js/gallant.js')){
			if(!is_dir(FOLDER_ROOT.'/cache')){
				mkdir(FOLDER_ROOT.'/cache', 0755);
			}
			if(!is_dir(FOLDER_ROOT.'/cache/js')){
				mkdir(FOLDER_ROOT.'/cache/js', 0755);
			}
			if(!is_file(GALLANT_CORE.\Gallant\GConst::GALLANT_JS_PARENT)){
				throw new \Gallant\Exceptions\CoreException('error GALLANT_JS_PARENT');
			}
			copy(GALLANT_CORE.\Gallant\GConst::GALLANT_JS_PARENT, FOLDER_ROOT.'/cache/js/gallant.js');
			chmod(FOLDER_ROOT.'/cache/js/gallant.js', 0755);
		}
		return '<script src="/cache/js/gallant.js" type="text/javascript"></script>';
	}

	private function htmlcompress($html) {
		//return $html;
		$tag_ignory = array('textarea','pre');
		preg_match_all('!([^<]+)!',$html,$pre);
		$html = preg_replace('![^<]+!', '#pre#', $html);
		$html = preg_replace('/[\r\n\t]?/', '', $html);
		foreach ($pre[0] as $tag) {
			if(!in_array(substr($tag,0,stripos($tag,' ')),$tag_ignory) &&
				!in_array(substr($tag,0,stripos($tag,'>')),$tag_ignory)){
				$tag = preg_replace('/[\r\n\t]?/', '', $tag);
                $tag = str_replace("  ",'', $tag);
            }
			$html = preg_replace('!#pre#!', $tag, $html, 1);
		}
		return $html;
	}
}