<?
/**
* Gallant\Components\Entry
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Components;

class Entry{
	/**
	* init
	* Это событие вызывается после инициализации фреймворка 
	* Используйте для дополнительной логики, например управление доступом
	*/
	static function init(){}

	/**
	* render
	* Это событие вызывается перед выводом результата
	* Используйте при необходимости изменить результаты вывода
	*/
	static function render(){}

	/**
	* destroy
	* Это событие вызывается перед завершением 
	* Используйте для коректного завершения ваших компонентов
	*/
	static function destroy(){}
}