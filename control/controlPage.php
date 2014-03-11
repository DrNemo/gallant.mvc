<?
namespace Control;
use \G as G;

class controlPage extends \Gallant\Prototype\controlDefault{
	
	function actionIndex(){
		G::template()->tpl('index');
	}

	function actionRoute(){
		$data['param'] = array(
			'param0' => G::getParam(),
			'param1' => G::getParam(true),
			'param2' => G::getParam(array('key1', 'key2', 'key3', 'key4')),

			'get' => G::getRequest('get')
			);
		G::template()->tpl('route', $data);
	}

	function actionG(){
		G::template()->tpl('g');
	}

	function actionConfig(){
		G::template()->tpl('config');
	}

	function actionControls(){
		G::template()->tpl('control');
	}

	function actionModels(){
		G::template()->tpl('model');
	}

	function actionGeneral(){
		G::template()->tpl('general');
	}

	function actionMethod(){
		G::template()->tpl('method');
	}
}