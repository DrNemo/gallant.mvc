<?
/**
* G
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

return array(
	'site' => array(
		'skin' => 'default',
		'main' => 'main',
		'chaster' => 'utf-8',
		'lang' => 'en',
		/** 
		* @todo: gzip, htmlcompress
		*/
		'gzip' => false,
		'htmlcompress' => false,
		),

	'route' => array(
		'type' => 'request_url', //'get',
		'index' => 'page/index',
		'error404' => 'action404'
		),

	'path' => array(
		'site' 		=> '%root%',
		'template' 	=> '%site%/template',
		'model' 	=> '%site%/model',
		'component' => '%site%/component',
		'include' 	=> '%site%/include',
		'control' 	=> '%site%/control',
		'lang' 		=> '%site%/data/lang',
		'entry'		=> '%root%/entry.php'
		),

	'db' => array()
);