<?
/**
* Entry - обработчик событий framework 
*/
use \G as G;

class Entry extends \Gallant\Components\Entry{

	/**
	* init - инициализация выполнена, G::getControl(), G::getAction(), G::getParam(), ... еще не доступны
	*/
	static function init(){
		//p('init framework');
		$url = G::route()->getUrl();
		
		$my_route = array(false, false);
		$url = array_diff_key ($url, $my_route);
		G::route()->setUrl($url);		
	}

	/**
	* load - определены G::getControl(), G::getAction(), G::getParam(), ...
	*/
	static function load(){
		/*p('load framework');
		
		p(
			G::getControl(), G::getAction(), G::getParam()
		);*/
	}

	/**
	* render - вызывается перед финальным рендером
	*/
	static function render(){
		//p('render framework');
		G::template()->setMeta(array('title'=>' Gallant Framework : '.G::version()));
		G::template()->setJs('jquery-2.0.3.min.js', 'bootstrap.min.js');
		G::template()->setCss('bootstrap.min.css');
	}

	/**
	* destroy - завершение работы, перед чисткой
	*/
	static function destroy(){
		//p('destroy framework');

	}
}









