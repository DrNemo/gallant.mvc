<?
namespace Control;
use \G as G;

class controlPage{

	function actionIndex(){
		G::template()->tpl('template-index');
	}

	function actionPage1(){
		$return_data = array(
			'urlParam' => G::getParam()
			);

		G::template()->tpl('template-page1', $return_data);
	}

	function action404(){
		p('404');
	}

}