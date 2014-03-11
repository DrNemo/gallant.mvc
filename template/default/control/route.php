<h1>Роутинг</h1>
<p>
	Запрошенный url <code>G::route()->getRoutes()</code><br>Запрошенный url для этой страницы: <?p(G::route()->getRoutes())?>
	Для изменения вызываемого url используйте <code>G::route()->setRoutes('my/new/url')</code><br><br>
	Текущая страница доступна по адресам: <code>/route</code> и <code>/page/route</code>, контроллер этой страницы <code>/example/control/controlPage.php</code><br>
	Url текущего контроллера <code>G::route()->getUrlStr()</code>: <code><?=G::route()->getUrlStr()?></code><br>
	Полный url текущего контроллера <code>G::route()->getPathStr()</code>: <code><?=G::route()->getPathStr()?></code><br>
	Текущий контроллер: <code>G::getControl()</code> или <code>G::route()->getControl()</code>: <code><?=G::getControl()?></code><br>
	Текущий action: <code>G::getAction()</code> или <code>G::route()->getAction()</code>: <code><?=G::getAction()?></code><br>
</p>

<p>
	<h2>Дополнительные параметры</h2>
	Передача дополнительных параметров через адресную строку <code>G::getParam()</code> или <code>G::route()->getParam()</code>:<br>
	<b>Массив параметров</b> пример адреса <a href="/route/value1/val2/val3">/route/value1/val2/val3</a>
	<ul>
		<li>
			<code>G::getParam()</code><br>
			Результат:
			<?p($Result->param['param0'])?>
		</li>
		<li>
			<code>G::getParam(true)</code><br>
			Результат:
			<?p($Result->param['param1'])?>
		</li>
		<li>
			<code>G::getParam(array('key1', 'key2', 'key3', 'key4'))</code><br>
			Результат:
			<?p($Result->param['param2'])?>
		</li>
	</ul>
</p>
<p>
	<h2 id="get">Получение GET</h2>
	Пример для GET <code>G::getRequest('get')</code>: <a href="/route/value1/val2/val3?get1=val1&get2=val2#get">/route/value1/val2/val3?get1=val1&get2=val2</a><br>
	Результат:
	<?p($Result->param['get'])?>

	<h2 id="post">Получение POST</h2>
	<?
	$post_form = G::getRequest('post', 'form_data');
	?>
	Пример для POST <code>G::getRequest('post', 'form_data')</code>
	<form method="post" action="#post">
		<input name="form_data[input1]" type="text" class="form-control" value="<?=$post_form['input1']?>"><br>
		<input name="form_data[input2]" type="text" class="form-control" value="<?=$post_form['input2']?>"><br>
		<input type="submit" class="btn">
	</form>
	Результат:
	<?p($post_form)?>
</p>
