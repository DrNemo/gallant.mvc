<?
/** @var $this Gallant\Components\Template */
?><h1>"Точка G"</h1>
<p>
	Большую часть взаимодействия с фраймворком вы будите выполнять через класс <code>\G</code>, для удобства рекомендуем в начале писать: <code>use \G as G;</code><br>
	Все метода этого класса доступны в любом месте проекта
</p>
<ul>
	<li>
		<code>G::init($path_to_config)</code> - <strong>Инциализация фреймворка</strong>
	<br><br></li>
	<li>
		<code>G::template()</code> - <strong>Управление шаблоном</strong>
	<br><br></li>
	<li>
		<code>G::getConfig($key)</code> - Возвращяет блок настроек с указанным ключем. <br>
		<code>G::getConfig('db')</code> - вернет настройки БД из конфига
	<br><br></li>
	<li>
		<code>G::set($key, $val)</code> - <strong>Добавить данные</strong> с ключем $key
	<br><br></li>
	<li>
		<code>G::get($key)</code> - <strong>Возвращяет данные</strong> с ключем $key. 
	<br><br></li>
	<li>
		<code>G::getDomen()</code> - <strong>Домен сайта</strong>
	<br><br></li>
	<li>
		<code>G::getPath($key)</code> - <strong>Путь</strong>. Вернет путь с ключем $key из блока настроек path
	<br><br></li>
	<li>
		<code>G::isRequest($type)</code> - <strong>Проверка запроса</strong>. Указывает был ли выполнен запрос $type: <code>post, get, files</code>
	<br><br></li>
	<li>
		<code>G::getRequest($type, $key = false, $filter = 'html')</code> - <strong>Возвращяет запрос</strong><br>
		$type: <code>post, get, files</code><br>
		$key: <code>string/false</code>
	<br><br></li>
	<li>
		<code>G::includeComponent($path)</code> - <strong>Подключение внешних компонентов</strong><br>
		<b>$path</b> - путь к подключаемому файлу из папки include
	<br><br></li>
	<li>
		<code>G::ref($url)</code> - <strong>Выполняет редирект</strong><br>
		Выполняет редирект по задоному урлу.
	<br><br></li>


	<li><h2>Работа с сессиями</h2></li>
	<li>
		<code>G::setSession($key, $value, $close = false)</code> - <strong>Установка данных сессии</strong><br>
		<b>$key</b> - Ключ сессии<br>
		<b>$value</b> - Данные сессии<br>
		<b>$close</b> - true запрещяет изменение данных сессии через метод setSession<br>
	<br><br></li>
	<li>
		<code>G::getSession($key)</code> - <strong>Возвращяет данные сессии</strong>
	<br><br></li>
	<li>
		<code>G::removeSession($key)</code> - <strong>Удаление сессии</strong>
	<br><br></li>


	<li><h2>Роутинг</h2>
		Подробнее о <a href="/route">роутинге</a>
	</li>
	<li>
		<code>G::route()</code> - <strong>Роутинг</strong>
	<br><br></li>
	<li>
		<code>G::getControl()</code> - <strong>Возвращяет текущий контроллер</strong>
	<br><br></li>
	<li>
		<code>G::setControl($control)</code> - <strong>Устанавливает новый конртоллер</strong>
	<br><br></li>
	<li>
		<code>G::getAction()</code> - <strong>Возвращяет текущий экшен</strong>
	<br><br></li>
	<li>
		<code>G::setAction($action)</code> - <strong>Устанавливает новый экшен</strong>
	<br><br></li>
	<li>
		<code>G::getParam()</code> - <strong>Возвращяет параметры на основе фильтра</strong>
	<br><br></li>


	<li><h2>Работа с БД</h2></li>
	<li>
		<code>G::DB($key)</code> - <strong>Возвращяет подключение к БД</strong>, $key - ключ поключения из конфига
	<br><br></li>
	<li>
		<code>G::dbQuery($key)</code> - <strong>Конструктор запросов</strong>. Вернет конструктор запросов
	<br><br></li>


	<li><h2>Управление ошибками</h2></li>

	<li>
		<code>G::setError($code, $val)</code> - <strong>Регистрирует ошибку</strong><br>
		<b>$code</b> - Ключ ошибки<br>
		<b>$val</b> - Описание ошибки<br>
	<br><br></li>

	<li>
		<code>G::getError($code)</code> - <strong>Возвращяет описание ошибки</strong> или true, если ошибка была установленна через setError
	<br><br></li>

	<li>
		<code>G::getErrorKeys()</code> - <strong>Ключи всех установленных ошибок</strong>
	<br><br></li>

	<li><h2>Flash messages</h2></li>
	<li>
		<code>G::setFlag($name_flag, $val_flag)</code> - <strong>Установка флага</strong>
	<br><br></li>

	<li>
		<code>G::getFlag($name_flag)</code> - <strong>Возвращяет и удаляет флаг</strong>
	<br><br></li>
</ul>