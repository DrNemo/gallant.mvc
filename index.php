<?
define('GALLANT_SYSTEM', true); 

define('FOLDER_ROOT', __DIR__); // корневая папка сайта

$config = FOLDER_ROOT.'/config.example.php'; // путь к файлу настроек

$gallant = FOLDER_ROOT.'/gallant/entry.php'; // путь к entry.php из пакета Gallant

if(is_file($gallant)){
	include $gallant;
}else die('Error patch gallant/entry.php');

G::init($config);

