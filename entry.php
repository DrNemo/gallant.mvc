<?
use \G as G;

/**
* Entry
* класс событий
*/
class Entry extends \Gallant\Components\Entry{
	/**
	* init
	* Это событие вызывается после инициализации фреймворка 
	*/
	static function init(){
		/*
		Устанавливаем значения meta-тегов, изменить их можно в любом контреллере
		*/
		G::template()->setMeta(array(
			'title' => 'Gallant - mvc php framework',
			'keywords' => 'Gallant, framework, php, mvc, ActiveRecord',
			'description' => 'Gallant - mvc php framework'
		));
	}
	
}
