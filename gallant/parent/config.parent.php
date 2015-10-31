<?
/**
* pre-config. default config framework
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

use \Gallant\Components\Entry;

return [
	'site' => [
		'skin' => 'default',
		'main' => 'main.php',
		'chaster' => 'utf-8',
		'lang' => 'en',
		'gzip' => false,
		'htmlcompress' => false,
		],

	'route' => [
		'site' 		=> '/', 
		'type' => 'get', // or get
		'index' => 'page/index',
		'error404' => 'action404'
		],

	'path' => [
		'template' 	=> '%site%' . DIRECTORY_SEPARATOR . 'template',
		'model' 	=> '%site%' . DIRECTORY_SEPARATOR . 'model',
		'component' => '%site%' . DIRECTORY_SEPARATOR . 'component',
		'include' 	=> '%site%' . DIRECTORY_SEPARATOR . 'include',
		'controller' 	=> '%site%' . DIRECTORY_SEPARATOR . 'controller',
		'entry'		=> '%site%' . DIRECTORY_SEPARATOR . 'entry.php',
		'widgets'	=> '%site%' . DIRECTORY_SEPARATOR . 'widget',
		],

	'db' => [],

	'recaptcha' => [],/* publicKey, privateKey */

	'bootstrap' => Entry::class,

	'class' => [],
];