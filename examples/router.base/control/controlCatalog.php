<?
namespace Control;
use \G as G;

class controlCatalog{

	function actionIndex(){
		/**
		* @param print_r mixed
		*/
		p('site/catalog/page/index');
	}

	function actionDetail(){		
		p('site/catalog/page/detail');
	}
}