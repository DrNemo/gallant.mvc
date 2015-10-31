<?
$posts = $Result->posts;
$count_page = $Result->count_page;
$curent_page = $Result->page;
?>
<h1>Example Blog</h1>
<?=$this->layer('layer-blog-menu')?>
<?foreach ($posts as $post) {?>
	<div class="post_item">
		<h2><a href="/blog/detail/<?=$post->id?>"><?=$post->title?></a></h2>
		<p><?=$post->content?></p>
		<div class="well well-sm row">
			<div class="col-md-2">
				<a href="/blog/detail/<?=$post->id?>"><span class="glyphicon glyphicon-comment"></span> <?=$post->related('comment')->count()?> comments</a>
			</div>
			<div class="col-md-2">
				<a href="/blog/auter/<?=$post->related('auter')->id?>"><span class="glyphicon glyphicon-user"></span> <?=$post->related('auter')->name?></a>
			</div>
			<div class="col-md-8"></div>
		</div>
	</div>
<?}?>

<?
$prev_class = ($curent_page > 0) ? '' : 'disabled';
$next_class = ($count_page - 1 > $curent_page) ? '' : 'disabled';
?>
<ul class="pagination">
	<li class="<?=$prev_class?>"><a href="/blog/index/<?=($curent_page - 1)?>">&laquo;</a></li>
	<?for($page = 0; $page < $count_page; $page++){
		$class = ($curent_page == $page) ? 'active' : '';
		?>
		<li class="<?=$class?>"><a href="/blog/index/<?=$page?>"><?=($page + 1)?></a></li>
		<?
	}
	?>
	<li class="<?=$next_class?>"><a href="/blog/index/<?=($curent_page + 1)?>">&raquo;</a></li>
</div>