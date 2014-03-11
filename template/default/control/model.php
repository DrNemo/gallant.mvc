<h1>Модели</h1>
<p>
	Описания моделей осуществляетя переопределением методов класса <code>\Gallant\Ar\Model</code><br>
	<code>class Post extends \Gallant\Ar\Model</code><br>

	<span class="label label-danger">!</span> - Обязательная для переопределения функция<br>
</p>
<h2>Методы</h2>
<ul>
	<li>
		<b>Ключ провайдера</b> - Укажите ключ из <a href="/config">настроек</a>, раздел db<br>
		Если его не указывать будет взят первый ключ из массива db
<pre>function provider(){
	return 'db.prod';
}</pre>
	</li>

	<li>
		<span class="label label-danger">!</span>
		<b>Имя таблицы в БД</b>, если имя таблицы нужно использовать без префикса, в начале поставьте ! <code>return '!users';</code>
<pre>function table(){
	return 'post';
}</pre>
	</li>

	<li>
		<span class="label label-danger">!</span>
		<b>Primary Key</b> - ключевое поле. Если ключ составной, то возвращяйте массив <code>return array('id', 'pid');</code>
<pre>function primaryKey(){
	return 'id';
}</pre>
	</li>

	<li>
		<span class="label label-danger">!</span>
		<b>Структура модели</b> - Список полей модели которые нужно загружать.
		<div class="alert alert-info">
			Если вы переименовали поле в БД, не обязательно переберать весь проект и менять имя, достаточно: <code>'old_name' => array('db' => 'new_name')</code>
		</div>
<pre>function structure(){
	return array(
		'id' => array(),
		'id_auter' => array(),
		'title' => array(),
		'content' => array(),
		...
	);
}</pre>
	</li>



	<li><b>Связи с другими моделями</b> - Основное приемущество шаблона Active Record<br>
		Описание свойств связей:
		<ul>
			<li><code>'model' => '\Model\MyModel'</code> - Полное имя модели</li>
			<li>
				<code>'relation' => self::ONE_TO_ONE</code> - Тип связи<br>
				Виды связей:<br>
				<ul>
					<li><code>self::ONE_TO_ONE</code> - "один к одному", у этой модели может быть только одна модель типа MyModel (например Post => Auter)</li>
					<li><code>self::ONE_TO_MANY</code> - "один ко многим", у этой модели можт быть множество MyModel (например Post => Comment)</li>
					<li><code>self::MANY_TABLE_BUNCH</code> - частный случай "один ко многим", когда для связи используется дополнительная таблица</li>
				</ul>
			</li>
			<li><code>'my_column' => 'id_auter'</code> - Колонка связи в текущей модели, если совпадает с ее primaryKey можно не указывать</li>
			<li><code>'his_column' => 'module_id'</code> - Колонка связи в превязываемой модели, если совпадает с ее primaryKey можно не указывать</li>
			<li><code>'table' => 'user_in_module'</code> - Используется только в случае <code>self::MANY_TABLE_BUNCH</code>, для указания имени доп. таблицы</li>
			<li>
				<code>'load' => true</code> - Загружать всегда, вне зависимости от глубины связи<br>
				<code>'load' => false</code> - Загружать только при обращение <code>$model->related('key_relation')</code>
				<div class="alert alert-warning">
  					Необдуманное использование этого аргумента, может привести к значительной потери производительности.
				</div>
			</li>
		</ul>
<pre>function relations(){
	return array(			
		'auter' => array(
			'model' => '\Model\Auter',		
			'relation' => self::ONE_TO_ONE,
			'my_column' => 'id_auter'
			)
	);
}</pre>
	</li>
</ul>
Пример модели:
<pre>namespace Model;
use \G;

class Post extends \Gallant\Ar\Model{
	function provider(){
		return 'db.prod';
	}

	function table(){
		return 'post';
	}

	function primaryKey(){
		return 'id';
	}

	function relations(){
		return array(			
			'auter' => array(
				'model' => '\Model\Auter',	
				'relation' => self::ONE_TO_ONE,
				'my_column' => 'id_auter'
				)
		);
	}

	function structure(){
		return array(
			'id' => array(),
			'id_auter' => array(),
			'title' => array(),
			'content' => array()
			);
	}
}</pre>