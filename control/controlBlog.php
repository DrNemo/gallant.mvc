<?
namespace Control;
use \G;
use \Model\Blog\Post;
use \Model\Blog\Auter;

class controlBlog extends \Gallant\Prototype\controlDefault{
	
	function actionIndex(){
		$url_param = G::getParam();
		$page = isset($url_param[0]) ? $url_param[0] : 0;
		$count_post = 10;

		$count_page = Post::fetch()->count() / $count_post;

		$criteria = Post::criteria()->limit($page * $count_post, $count_post)->order('id');
		$posts = Post::fetch($criteria);
		if(!$posts){
			G::ref('/blog/');
		}

		G::template()->tpl('blog/index', array(
			'posts' => $posts, 
			'page' => $page, 
			'count_page' => $count_page
			));
	}


	function actionAuter(){
		$url_param = G::getParam();
		$auter_id = isset($url_param[0]) ? $url_param[0] : 0;
		$auter = Auter::fetchPk($auter_id)->first();
		if(!$auter){
			G::ref('/blog/');
		}
		G::template()->tpl('blog/auter', array('auter' => $auter));
	}

	function actionDetail(){
		$url_param = G::getParam();
		$post_id = isset($url_param[0]) ? $url_param[0] : 0;
		$post = Post::fetchPk($post_id)->first();
		if(!$post){
			G::ref('/blog/');
		}
		G::template()->tpl('blog/detail', array('post' => $post));
	}
}