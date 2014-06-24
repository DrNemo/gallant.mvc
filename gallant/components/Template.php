<?
/**
* Gallant\Components\Template
* 
* @package Gallant
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Components;

use \G;
use \Gallant\Exceptions\CoreException;
use \Gallant\GConst;


class Template{
	private $skin = false;
	private $main = false;

	private $folder_template;
	private $folder_template_http;

	private $chaster = 'utf-8';

	private $content_controller = '';
	private $content_page = '';

	private $gzip = false;
	private $htmlcompress = false;

	private $folder;

	private $meta = array(
		'title' => '',
		'description' => '',
		'keywords' => ''
		);

	function __construct(){
		ob_start();
		$design = G::getConfig('site');
		$this->chaster = $design['chaster'];

		header("Content-Encoding: ".$this->chaster);
		header("Content-Type: text/html; charset=".$this->chaster);

		header("Content-language: ".$design['lang']);

		$this->gzip = $design['gzip'];
		$this->htmlcompress = $design['htmlcompress'];
		
		$this->setSkin($design['skin']);
		$this->setMain($design['main']);
	}

	function setSkin($skin){
		$folders = G::getPath('template');
		$templ = false;
		if(is_array($folders)){
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
			throw new CoreException('error template not folder template');
		}else if(!is_dir($templ.$skin)){
			throw new CoreException('error template skin folder: '.$skin);
		}

		$this->folder_template = $templ;

		$http_path = substr($templ, strlen(FOLDER_ROOT));
		if(DIRECTORY_SEPARATOR == '\\'){
			$http_path = str_replace(DIRECTORY_SEPARATOR, '/', $http_path);
		}

		$this->folder_template_http = $http_path;
		$this->skin = $skin;

		$this->folder = array(
			'skin' => $this->folder_template_http.$this->skin.'/',
			'js' => $this->folder_template_http.$this->skin.'/js/',
			'images' => $this->folder_template_http.$this->skin.'/images/',
			'css' => $this->folder_template_http.$this->skin.'/css/',
			'widget' => $this->folder_template_http.$this->skin.'/widget/',
			);
	}

	function getSkin(){
		return $this->skin;
	}

	function setMain($main){
		$file = $this->folder_template.$this->skin.DIRECTORY_SEPARATOR.$main;
		if(!is_file($file)){
			throw new CoreException('error template main: '.$this->skin.DIRECTORY_SEPARATOR.$main);
		}
		$this->main = $main;
	}

	function getMain(){
		return $this->main;
	}

	function ob(){
		$this->content_controller .= ob_get_clean();
		ob_start();
		return $this->content_controller;
	}

	function tpl($file = false, $Result = array()){
		$Result = (object)$Result;
		if($file){
			if(!is_file($this->folder_template.$this->skin . DIRECTORY_SEPARATOR . 'control' . DIRECTORY_SEPARATOR . $file . '.php')){
				throw new CoreException('Not file tpl: '.$file);
			}else{
				include $this->folder_template.$this->skin . DIRECTORY_SEPARATOR . 'control' . DIRECTORY_SEPARATOR . $file . '.php';
			}
		}
		return $this->ob();
	}

	function layer($file, $Result = array()){
		ob_start();
		$Result = (object)$Result;
		if(!is_file($this->folder_template.$this->skin . DIRECTORY_SEPARATOR . 'layer' . DIRECTORY_SEPARATOR . $file . '.php')){
			throw new CoreException('Not file layer: '.$file);
		}else{			
			include $this->folder_template.$this->skin. DIRECTORY_SEPARATOR . 'layer' . DIRECTORY_SEPARATOR . $file.'.php';			
		}
		$content = ob_get_clean();
		return $content;
	}

	function render($result = false){
		if(!G::isAjax()){
			ob_start();

			include $this->folder_template.$this->skin . DIRECTORY_SEPARATOR . $this->main;
			$this->content_page = ob_get_clean();
			ob_start();

			include $this->folder_template.$this->skin . DIRECTORY_SEPARATOR . 'head.php';
		
			$content = ob_get_clean();
			if($this->htmlcompress){
				$content = $this->htmlcompress($content);
			}			
		}else{
			$content = ob_get_clean() . $this->getContentController();
			if($this->htmlcompress){
				$content = $this->htmlcompress($content);
			}
			$return = array(
				'code' => 200,
				'result' => array(
					'data' => $result,
					'content' => $content
					)
				);
			$content = json_encode($return);
		}

		if($this->gzip){
			ob_start('ob_gzhandler');
		}
		echo $content;
	}

	
	function getContentController(){
		return $this->content_controller;
	}

	function getContentPage(){
		return $this->content_page;
	}

	private $file_js = array();
	function setJs(){
		$files = func_get_args();		
		if(is_array($files[0])) $files = $files[0];
		$this->file_js = array_merge($files, $this->file_js);
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
		if(is_array($files[0])) $files = $files[0];
		$this->file_css = array_merge($files, $this->file_css);
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

	/**
	* link
	* @param string $url - адрес страницы
	* @param array $arg - массив get параметров
	* @param string $anchor - якорь
	*/
	function link($url = false, $arg = false, $anchor = false){
		$route = G::getConfig('route');
		if(!$url) $url = G::route()->getUrlStr();
		if(!$url && !$arg) $arg = G::getRequest('get');
		$link = $url;
		if($arg){
			$f = function($key, $val){
				return "$key=$val";
			};
			$get = implode('&', array_map($f, array_keys($arg), array_values($arg)));
			$link .= '?'.$get;
		}
		if($anchor){
			$link .= '#'.$anchor;
		}
		if($route['type'] == 'get'){
			return '?route='.$link;
		}else{
			return $link;
		}
	}

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
			if(!is_file(GALLANT_CORE.GConst::GALLANT_JS_PARENT)){
				throw new CoreException('error GALLANT_JS_PARENT');
			}
			copy(GALLANT_CORE.GConst::GALLANT_JS_PARENT, FOLDER_ROOT.'/cache/js/gallant.js');
			chmod(FOLDER_ROOT.'/cache/js/gallant.js', 0755);
		}
		return '<script src="/cache/js/gallant.js" type="text/javascript"></script>';
	}

	private function htmlcompress($html){
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

	public function widget($name, $data = NULL){
		$widget_name = ucfirst(strtolower($name));

		$widget_class = '\Gallant\Widgets\\'.$widget_name.'\\Widget'.$widget_name;
		$widget_user_class = '\Widgets\\'.$widget_name.'\\Widget'.$widget_name;
		
		$widget = false;
		if(class_exists($widget_class)){
			$widget = new $widget_class($name, $data);			
		}
		if(class_exists($widget_user_class)){
			$widget = new $widget_user_class($name, $data);	
		}
		if(!$widget){
			throw new CoreException("not found widget: $name ($widget_class or $widget_user_class)");
		}
		return $widget;
	}

	public function warning($errors = false, $class = 'alert alert-warning'){
		if(!$errors){
			$errors = G::getErrorKeys();
		}else{
			$errors = is_array($errors) ? $errors : array($errors);
		}
		
		$func_tpl = function($val) use ($class){
			if($msg = G::getError($val)){
				return "<div class=\"{$class} {$val}\">{$msg}</div>";
			}else{
				return '';
			}
		};
		return implode(array_map($func_tpl, $errors));
	}
}