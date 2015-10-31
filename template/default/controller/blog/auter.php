<?
$posts = $Result->auter->related('posts');
$auter = $Result->auter;
?>
<h1>Posts <?=$auter->name?>: <?=$posts->count()?></h1>
<?=$this->layer('layer-blog-menu')?>


<?if($posts) foreach ($posts as $post) {?>
	<div class="post_item">
		<h2><a href="/blog/detail/<?=$post->id?>"><?=$post->title?></a></h2>
		<p><?=$post->content?></p>
	</div>
<?}?>