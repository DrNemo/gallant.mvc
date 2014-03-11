<?
namespace Control\Test;
use \G as G;

class controlMy extends \Gallant\Prototype\controlDefault{
	
	function actionIndex(){
		p('Control\Test\controlMy->actionIndex()');

		G::template()->tpl();
	}

	function actionMethod(){
		p('Control\Test\controlMy->actionMethod()');

		G::template()->tpl();
	}

	function ajaxTime(){
		return array('time' => 'My time: ' . date('H:i d.m.Y',time()));
	}

	
}