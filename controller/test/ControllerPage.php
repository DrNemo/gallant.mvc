<?
namespace Controller\Test;
use \G as G;
use \Gallant\Prototype\controlDefault;

class ControllerPage extends controlDefault{
	function __construct(){
		// Вы можете использовать __construct для задания поведения или свойств для всех action в этом controller
	}
	
	function actionIndex(){
		p('Control\Test\controlPage->actionIndex()');

		G::template()->tpl();
	}

	function actionNewAction(){
		p('Control\Test\controlPage->actionNewAction()');

		G::template()->tpl();
	}
}