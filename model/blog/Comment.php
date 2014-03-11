<?
namespace Model\Blog;
use \Gallant\Ar\Model;

class Comment extends Model{
	// Указываем какое подключение будем использовать, ключ указывается в config
	function provider(){
		return 'db.prod';
	}

	// Указываем какую таблицу использует модель
	function table(){
		return 'comment';
	}

	// Указываем primary key
	function primaryKey(){
		return 'id';
	}

	// Указываем связи с другими моделями
	function relations(){
		return array(			
			'auter' => array(
				'model' => '\Model\Blog\Auter',//  название модели			
				'relation' => self::ONE_TO_ONE,// тип связи "один к одному"
				'my_column' => 'id_auter', // имя связываемой колонки в текущей модели, если совпадает с primaryKey, можно не указывать
				//'his_column' => 'module_id' // имя связываемой колонки в \Model\Auter, если совпадает с ее primaryKey, можно не указывать
				'load' => true
				)
			);
	}

	// структура таблицы
	function structure(){
		return array(
			'id' => array(),
			'id_post' => array(),
			'id_auter' => array(),
			'comment' => array(),
			);
	}
}