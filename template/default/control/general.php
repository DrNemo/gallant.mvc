<h1>Active Record</h1>
<p>
	Подробнее о шаблоне проектирования Active Record можно почитать <a target="_blank" href="http://ru.wikipedia.org/wiki/ActiveRecord">тут</a> (http://ru.wikipedia.org/wiki/ActiveRecord)<br><br>

	<div class="alert alert-warning">
		Для локальной работы примеров, создайте БД из дампа лежащего на github и правильно настройте доступы в config
	</div>
	<a target="_blank" href="/blog/">Пример блога</a>
</p>
<h2>Получение моделей</h2>
<p>
	Оба метода вернут класс <code>Iterator</code> с найденными моделями
</p>
<h3>fetch</h3>
<p>
	<code>MyModel::fetch()</code> - вернет все экземпляры модели<br>
	Для более сложных выборок вы можете использовать класс <code>Criteria</code>:<br>
	<code>
		$criteria = MyModel::criteria()->limit(10);<br>
		MyModel::fetch($criteria); // вернет 10 экземпляров MyModel
	</code>
	<br><br>
	<b>Методы Сriteria</b>
	<ul>
		<li><code>where('id_auter = :auter')</code> - условия выборки</li> 
		<li><code>limit($limit [, $offset])</code> - срез из таблицы БД</li> 
		<li><code>order($column [, $order = 'ASC'])</code> - сортировка по основной модели</li> 
		<li>
			<code>orderRelation($relation_key, $column[, $order = 'ASC'])</code> - сортировка по связанной модели<br>
			<code>$relation_key</code> - ключ связанной модели из функции <code>relations</code><br>
			<code>$column</code> - имя колонки<br>
			<code>$order</code> - тип сортировки
		</li>
		<li>
			<code>attr(array(':auter' => 542))</code> - Установка атрибутов
		</li> 
		<li>
			<code>group($str)</code> - Установка группировки
		</li>
	</ul>
</p>
<h3>fetchPk</h3>
<code>MyModel::fetchPk($pk)</code> - вернет модель с указанным primaryKey

<h2>Создание и редактирование модели</h2>
<ul>
	<li>
		вариант 1:<br>
		<code>
			$data = array(<br>
				// имя колонки => значение<br>
				'column1' => 'value1',<br>
				'column2' => 'value2',<br>
				//...<br>
			);<br>
			$model = new MyModel($data);<br>
			$model->save(); // сохранение новой модели, вернет ее primaryKey
		</code>
	</li>
	<li>
		вариант 2:<br>
		<code>
			$model = new MyModel;<br>
			$model->column1 = 'value1';<br>
			$model->column2 = 'value2';<br>	
			//...<br>		
			$model->save(); // сохранение новой модели, вернет ее primaryKey
		</code>
	</li>
	<li>
		вариант 3:<br>
		<code>
			$data = array(<br>
				// имя колонки => значение<br>
				'column1' => 'value1',<br>
				'column2' => 'value2',<br>
				//...<br>
			);<br>
			$model = new MyModel;<br>
			$model->attr($data); // or $model->setData($data);<br>
			$model->save(); // сохранение новой модели, вернет ее primaryKey
		</code>
	</li>
	<li>Все варианты совместимы</li>
</ul>
<h2>Удаление модели</h2>
<p>
	Удаление загруженной модели<br>
	<code>
		$model = MyModel::fetchPk(785)->first(); // загрузим модель с primaryKey = 785<br>
		$model->delete(); // удаляем ее
	</code>
</p>