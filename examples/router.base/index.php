<?
define('GALLANT_SYSTEM', true); // GALLANT_SYSTEM - вход выполнен через через этот файл
define('FOLDER_ROOT', __DIR__); // FOLDER_ROOT - корневая директория

$config = FOLDER_ROOT.'/config.php'; // путь к конфигу
include $_SERVER['DOCUMENT_ROOT'].'/gallant/entry.php'; // include Gallant/entry.php

G::init($config); // start framework