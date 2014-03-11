<?
namespace Control\Test;
use \G as G;

class controlPage extends \Gallant\Prototype\controlDefault{
	function __construct(){
		// Вы можете использовать __construct для задания поведения или свойств для всех action в этом control
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