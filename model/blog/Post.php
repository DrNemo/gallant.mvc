<?
namespace Model\Blog;
use \Gallant\Ar\Model;

class Post extends Model{
	/*function __construct(&$data = false){
		p('NEXT POST');
		parent::__construct($data);
	}*/
	// Указываем какое подключение будем использовать, ключ указывается в config
	function provider(){
		return 'db.prod';
	}

	// Устанавливаем имя для sql-запросов
	function alias(){
		return 'post';
	}

	// Указываем какую таблицу использует модель
	function table(){
		return 'post';
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
				),
			'comment' => array(
				'model' => '\Model\Blog\Comment',//  название модели			
				'relation' => self::ONE_TO_MANY,// тип связи "один к многому"
				//'my_column' => 'id_auter', // имя связываемой колонки в текущей модели, если совпадает с primaryKey, можно не указывать
				'his_column' => 'id_post', // имя связываемой колонки в \Model\Comment, если совпадает с ее primaryKey, можно не указывать
				)
		);
	}

	// структура таблицы
	function structure(){
		return array(
			'id' => array(),
			'id_auter' => array('order' => 'ASC'),
			'title' => array(),
			'content' => array()
			);
	}
}