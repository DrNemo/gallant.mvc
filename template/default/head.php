<!DOCTYPE html>
<html lang="ru">
	<head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <title><?=$this->getMeta('title')?></title>
	    <meta name="Description" content="<?=$this->getMeta('description')?>" />
	    <meta name="Keywords" content="<?=$this->getMeta('keywords')?>" />

	    <?
	    // подключяем общие файлы для всео сайта
	    echo $this->includeCss('bootstrap.min.css', 'bootstrap-theme.min.css');
	    echo $this->includeJs('jquery-2.0.3.min.js', 'bootstrap.min.js');
	    ?>
	</head>
	<body>
		<div class="container"><?=$this->getContentPage()?></div>
		<footer>
			<div class="pull-right">
				Power by <a href="http://gallantes.ru/" target="_blank">Gallant v.: <?=G::version()?></a>
			</div>
			Code licensed under <a href="http://opensource.org/licenses/mit-license.html" target="_blank">MIT License</a> (2013 - <?=date('Y')?>)
		</footer>
	</body>
</html> 