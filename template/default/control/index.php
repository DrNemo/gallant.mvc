<h1>Gallant - open source PHP framework</h1>
<p>
	Gallant - это свободно распростроняемый MVC-framework.<br>
	Gallant не предъявляет особых требований к структуре вашего проекта, изначально framework разрабатывался для сложных проектов имеющих множество компоненотов и часто реализованных в виде отдельных сайтов.
</p>
<p>
	Структура проекта по-умолчанию:
	<ul>
		<li><b>Папки</b></li>
		<li><code>/gallant/</code> - Gallant framework</li>
		<li><code>/control/</code> - папка контроллеров</li>
		<li><code>/model/</code> - папка моделей</li>
		<li><b>Шаблоны</b></li>
		<li><code>/template/</code> - папка шаблонов</li>
		<li><code>/template/default/</code> - папка шаблона default</li>
		<li><code>/template/default/control/</code> - view для контроллеров</li>
		<li><code>/template/default/js/</code> - подключаемые js файлы</li>
		<li><code>/template/default/style/</code> - подключаемые стили</li>
		<li><code>/template/default/images/</code> - Графика используемая для этого шаблона</li>
		<li><code>/template/default/layer/</code> - папка для "свободных" слоев</li>
		<li><b>Файлы</b></li>
		<li><code>/.htaccess</code> - Настройки сервера отправляющие все запросы на <code>/index.php</code></li>
		<li><code>/index.php</code> - "Точка входа", тут подключается и инициализируется фреймворк</li>
		<li><code>/config.php</code> - Настройки проекта</li>		
		<li><code>/template/default/head.php</code> - Основной шаблон, мета-теги, подключение js и css, счетчики...</li>
		<li><code>/template/default/main.php</code> - Шаблон стуктуры страницы</li>
	</ul>
</p>

<h2>Установка</h2>
<p>
	Скачайте и разместите в корне вашего сайта последнюю версию фраймворка (<a target="_blank" href="https://github.com/DrNemo/gallant.mvc">тут</a>)<br>
	<code>git clone https://github.com/DrNemo/gallant.mvc.git</code>
</p>

<h2>Инициализация и запуск</h2>
<p>
	Код <code>/.htaccess</code>
	<pre>
AddDefaultCharset utf-8
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule . index.php		
	</pre>

	Код <code>/index.php</code>
	<pre>
define('GALLANT_SYSTEM', true); // Обезательная константа, указывает что вход выполнен именно из этого файла
define('FOLDER_ROOT', __DIR__); // корневая папка сайта
define('FOLDER_SITE', FOLDER_ROOT); // папка сайта

$config = FOLDER_SITE.'/config.example.php'; // путь к файлу настроек

$gallant = FOLDER_ROOT.'/gallant/entry.php'; // путь к файлу entry.php из пакета Gallant

if(is_file($gallant)){
	include $gallant;
}else die('Error patch gallant/entry.php');

G::init($config); // Запускаем Gallant
	</pre>
</p>