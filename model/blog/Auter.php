<?
namespace Model\Blog;
use \Gallant\Ar\Model;

class Auter extends Model{
	// Указываем какое подключение будем использовать, ключ указывается в config
	static function provider(){
		return 'db.prod';
	}

	// Указываем какую таблицу использует модель
	static function table(){
		return 'auter';
	}

	// Указываем primary key
	static function primaryKey(){
		return 'id';
	}

	// Указываем связи с другими моделями
	static function relations(){
		return array(			
			'posts' => array(
				'model' => '\Model\Blog\Post',//  название модели	
				'relation' => self::ONE_TO_MANY,// тип связи "один к многому"
				'his_column' => 'id_auter',// имя связываемой колонки в \Model\Post, если совпадает с ее primaryKey, можно не указывать
				)
		);
	}

	// структура таблицы
	static function structure(){
		return array(
			'id' => array(),
			'name' => array()
			);
	}
}