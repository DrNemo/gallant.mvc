<?
namespace Gallant\Prototype;
use \G;
use Gallant\Components\Controller;

class controlDefault extends Controller{
	function action404(){
		G::template()->tpl('error-page/404');
	}
}