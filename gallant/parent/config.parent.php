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

return array(
	'site' => array(
		'skin' => 'default',
		'main' => 'main.php',
		'chaster' => 'utf-8',
		'lang' => 'en',
		'gzip' => false,
		'htmlcompress' => false,
		),

	'route' => array(
		'site' 		=> '/', 
		'type' => 'request', // or get
		'index' => 'page/index',
		'error404' => 'action404'
		),

	'path' => array(
		'template' 	=> '%site%' . DIRECTORY_SEPARATOR . 'template',
		'model' 	=> '%site%' . DIRECTORY_SEPARATOR . 'model',
		'component' => '%site%' . DIRECTORY_SEPARATOR . 'component',
		'include' 	=> '%site%' . DIRECTORY_SEPARATOR . 'include',
		'control' 	=> '%site%' . DIRECTORY_SEPARATOR . 'control',
		'entry'		=> '%site%' . DIRECTORY_SEPARATOR . 'entry.php'
		),

	'db' => array(),

	'recaptcha' => array(),/* publicKey, privateKey */

	'class' => array(),
);