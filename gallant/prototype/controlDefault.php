<?
namespace Gallant\Prototype;
use \G;

class controlDefault{
	function action404(){
		G::template()->tpl('error-page/404');
	}
}