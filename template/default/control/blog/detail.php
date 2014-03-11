<?
$post = $Result->post;
$comments = $post->related('comment');
?>
<h1><?=$post->title?></h1>
<?=$this->layer('layer-blog-menu')?>
<p><?=$post->content?></p>
<div>
	<span class="glyphicon glyphicon-user"></span> Auter: 
	<a href="/blog/auter/<?=$post->related('auter')->id?>"><?=$post->related('auter')->name?></a>
</div>

<h2><span class="glyphicon glyphicon-comment"></span> Comments (<?=$comments->count()?>)</h2>

<?foreach ($comments as $comment){?>
	<blockquote>		
		<?=$comment->comment?>
		<div class="row">
			<div class="col-md-3">
				<span class="glyphicon glyphicon-user"></span> Auter: 
				<a href="/blog/auter/<?=$comment->related('auter')->id?>"><?=$comment->related('auter')->name?></a>
			</div>
		</div>
	</blockquote>
<?}?>
