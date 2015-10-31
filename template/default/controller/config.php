<?
/** @var $this Gallant\Components\Template */
?>
<h1>Настройка Gallant</h1>
<p>Основная настройка фрейморка производится в файле config.php</p>
<ul>

	<li>
		<code>path</code> - Настройки путей. Все пути поддерживают переменные:<br>
		<code>%root%</code> - Корень сайта (<code>FOLDER_ROOT</code>)<br>
		<code>%site%</code> - Корень проекта (<code>FOLDER_SITE</code>)
		<ul>
			<li><code>control</code> - Папка контроллеров, поддерживает массив путей (default: <code>%site%/control</code>)</li>
			<li><code>model</code> - Папка моделей, поддерживает массив путей (default: <code>%site%/model</code>)</li>
			<li><code>template</code> - Папка шаблонов, поддерживает массив путей (default: <code>%site%/template</code>)</li>
			<li><code>component</code> - Папка дополнительных компонентов, ваши вспомогательные файлы (default: <code>%site%/component</code>)</li>
			<li><code>include</code> - Папка для чужих пакетов, например phpMailer (default: <code>%site%/include</code>)</li>
			<li><code>entry</code> - Путь к entry.php с классом Entry (default: <code>%site%/entry.php</code>)</li>
		</ul>
	</li>

	<li>
		<code>site</code> - настойки сайта
		<ul>
			<li><code>skin</code> - Имя шаблона, должно совпадать с название его папки (default: <code>default</code>)</li>
			<li><code>main</code> - Имя файла структуры шаблона (default: <code>main.php</code>)</li>
			<li><code>chaster</code> - Кодировка сайта (default: <code>utf-8</code>)</li>
			<li><code>lang</code> - Язык сайта (default: <code>en</code>)</li>
			<li><code>gzip</code> - сжатие GZip, true/false (default: <code>false</code>)</li>
			<li><code>htmlcompress</code> - обфуксация html true/false (default: <code>false</code>)</li>
		</ul>
	</li>

	<li>
		<code>route</code> - Настройки рутинга
		<ul>
			<li><code>site</code> - Корень сайта (default: <code>/</code>)</li>
			<li>
				<code>type</code> - Тип роутинга (request_url / get) (default: <code>request</code>)<br>
				request - роутинг на основе url<br>
				get - адрес контроллера берется из $_GET['route']
			</li>
			<li><code>index</code> - Имя контроллера и экшена отрабатывающие index-файлы (default: <code>page/index</code>)</li>
			<li><code>error404</code> - Имя экшена для обработки 404 ошибки (default: <code>action404</code>)</li>
		</ul>
	</li>

	<li>
		<code>db</code> - Настройки соединения с БД (на текущий момент поддерживается только MySql)
		<pre>
'db_key' => array(
	'provider' => 'mysql', // Провайдер БД (на текущий момент поддерживается только MySql)
	'host' => 'localhost', // Сервер БД
	'table' => 'db_table', // Имя базы данных
	'user' => 'root', // Пользователь
	'pass' => '', // Пароль
	'pref' => 'my_pref_', // ваш префикс для таблиц
	'character' => 'utf8' // Кодировка подключения
), ...
		</pre>
	</li>

	<li>
		<code>recaptcha</code> - Настройки <a href="http://www.google.com/recaptcha" target="_blank">reCaptcha</a>
		<ul>
			<li><code>publicKey</code></li>
			<li><code>privateKey</code></li>
		</ul>
	</li>
</ul>

<p>Пример файла config.php</p>
<pre>
return array(
	'site' => array(
		'skin' => 'default',
		'main' => 'main.php',
		'chaster' => 'utf-8',
		'lang' => 'en'
		),

	'route' => array(
		'index' => 'page/index',
		),

	'path' => array(
		'template' 	=> '%site%/template',
		'model' 	=> '%site%/model'
		)
);
</pre>
