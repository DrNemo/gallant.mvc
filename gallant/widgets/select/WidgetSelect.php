<?
/**
* Gallant\Widgets\Select\Widget
*
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Widgets\Select;

use \G;

class WidgetSelect extends \Gallant\Widgets\Controler{

	protected $config = array(
		'key' => 'id',
		'value' => 'title',
		'select' => false
		);

	public function select($sel){
		$this->config['select'] = $sel;
		return $this;
	}

	public function dir(){
		return __DIR__;
	}

}