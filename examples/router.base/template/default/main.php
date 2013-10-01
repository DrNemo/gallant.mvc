<?
$menu = array(
	'/' => array('Helloy World', '/page/index'),
	'/page1/param1/param2' => array('Control Params', '/page/page1'),
	'/catalog/' => array('Catalog', '/catalog/index'),
	'/catalog/detail' => array('Catalog Action', '/catalog/detail'),
	);
$action = G::route()->getStrUrl();
?>
<div class="container">
	<h1><?=G::getControl().'::'.G::getAction()?></h1>
	<h4>Gallant ver: <?=G::version()?></h4>
	<div>
		<ul class="nav nav-tabs">
			<?foreach($menu as $url => $val){
				$class = false;
				if($val[1] == $action){
					$class = ' class="active"';
				}
				?>
				<li<?=$class?>><a href="/examples/router.base<?=$url?>"><?=$val[0]?></a></li>
			<?}?>
		</ul>
	</div>
	<?=$this->content?>
</div>