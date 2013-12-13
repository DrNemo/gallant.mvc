<?
/**
* entry.php
* Gallant php framework
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

if(!defined('GALLANT_SYSTEM') || GALLANT_SYSTEM !== true) die('NOT GALLANT_SYSTEM');
define('GALLANT_CORE', __DIR__);
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);

if(defined('FOLDER_ROOT')){
	define('FOLDER_ROOT', SITE_ROOT);
}

include GALLANT_CORE.'/AutoLoading.php';

function p(){
	$p=func_get_args();

	echo '<pre>';
	$fs = debug_backtrace(false);
	foreach($fs as $f){
		if($f['function'] == 'p'){
			echo 'Print debug Function: '.$f['file'].':'.$f['line'].''."\n";
		}
	}
	foreach($p as $pp){
		print_r($pp);
		echo "\n\r";
	}
	echo '</pre>';
}