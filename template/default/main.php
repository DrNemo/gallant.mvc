<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <a class="navbar-brand" href="/">Gallant - open source MVC framework</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a target="_blank" href="/doc/">Документация (phpDoc)</a></li>
        <li><a target="_blank" href="https://github.com/DrNemo/gallant.mvc"><img src="<?=$this->getFolder('images')?>github.png"> GitHub Source</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="row">
	<div class="col-md-3">
		<?
		// выводим рендер свободного слоя, в этом примере оно содержит меню
		echo $this->layer('layer-menu');
		?>
	</div>
	<div class="col-md-9">
		<?
		// выводим результат рендера вызванного контроллера
		echo $this->getContentController();
		?>
	</div>
</div>
