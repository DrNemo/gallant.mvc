<?
/** @var $this Gallant\Components\Template */
?>
<h1>Контроллеры</h1>
<p>
	Контроллеры в Gallant играют роль группы страниц, где каждый экшен - это отдельная страница.<br><br>
	Рекомендуется объединять в один контроллер, экшены обрабатывающие данные одной Модели.<br>
	Например <code>controlBlog</code>, содержащий экшены<br>
	<code>actionIndex</code> - Список постов в блоге<br>
	<code>actionSave</code> - Сохранение изменений в записе, данный экшен можно объединить с <code>actionAdd</code>, однако это отается на ваше усмотрение<br>
	<code>actionDetail</code> - Детальный просмотр поста и комментариев к нему<br><br>

	Примеры различного расположения контроллеров:<br>
	<a href="/test/" target="_blank">/control/test/controlPage -> actionIndex()</a><br>
	<a href="/test/newaction" target="_blank">/control/test/controlPage -> actionNewAction()</a><br>
	<a href="/test/my/" target="_blank">/control/test/controlMy -> actionIndex()</a><br>
	<a href="/test/my/method" target="_blank">/control/test/controlMy -> actionMethod()</a><br><br>
</p>

<h2>Ajax экшены</h2>
<p>
	Gallant так же поддерживает ajax экшены, в отличее от простых доступ к ним возможет только через XMLHttpRequest.<br>
	Ajax экшен начинается с префикса <code>ajax</code>, обращяться к ним нужно так же как и к обычным экшенам<br>
	Экшен так же может возвращять отрендеренный шаблон.<br><br>
	<button id="server_time" class="btn btn-primary">What time do you have?</button>
	<script type="text/javascript">
	$(document).ready(function(){
		$('#server_time').click(function(){
			$.ajax({
				type: "POST",
				cache: false,
				url: '/test/my/time',
				dataType: 'json',
				success: function(msg){
					console.log(msg['result']);
					alert(msg['result']['data']['time']);
				}
			});
		})
	});
	</script>
</p>

<h3>Пример контроллера:</h3>
<pre>
namespace Control;
use \G as G;

class controlBlog extends \Gallant\Prototype\controlDefault{
	
	function actionIndex(){
		# my code
	}

	function actionSave(){
		# my code
	}

	function actionDetail(){
		# my code
	}

	function ajaxMethod(){
		# my code in ajax action
	}
}
</pre>

<h3>Прототипы контроллеров</h3>
<p>
	Наследование контроллера от прототипа не обязательно. Однако убедитесь в наличие action404 минимум у корневого контроллера <code>controlPage/action404</code>
	<ul>
		<li><code>\Gallant\Prototype\controlDefault</code> - прототип с action404</li>
	</ul>
</p>
