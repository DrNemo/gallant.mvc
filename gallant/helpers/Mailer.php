<?
/**
* G
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Helpers;
use \G as G;

class Mailer{
	private $option = false;
	private $phpmailer = false;

	private $name = 'auto mailer';
	private $content = false;

	function __construct($key){
		$mails = G::getConfig('mailer');
		if(!$mails){
			throw new \Gallant\Exceptions\HelperException('error config mailer');
		}
		if(!isset($mails[$key])){
			throw new \Gallant\Exceptions\HelperException('error config mailer: '.$key);
		}

		G::includeComponent('PHPMailer/class.phpmailer.php');

		$this->option = (object)$mails[$key];

		if(isset($this->option->name)) $this->name = $this->option->name;

		$this->phpmailer = new \PHPMailer();

		$this->phpmailer->CharSet = 'utf-8';
		$this->phpmailer->SetLanguage('ru');

		if($this->option->provider == 'smtp'){
			$this->phpmailer->IsSMTP();	

			$this->phpmailer->SMTPDebug  = $this->option->debug;
			$this->phpmailer->SMTPAuth   = $this->option->auth;

			if(isset($this->option->secure)){
				$this->phpmailer->SMTPSecure = $this->option->secure;
			}

			$this->phpmailer->Host       = $this->option->host;
			$this->phpmailer->Port       = $this->option->port;
			$this->phpmailer->Username   = $this->option->user; 
			$this->phpmailer->Password   = $this->option->password;
		}else{
			throw new \Gallant\Exceptions\HelperException('no implementation provider: '.$this->option->provider);
		}		
	}

	function setTo($mail, $name = ''){
		$this->phpmailer->AddAddress($mail, $name);
		return $this;
	}

	function setTitle($title){
		$this->phpmailer->Subject = $title;
		return $this;
	}

	function setContent($content){
		$this->content = $content;
		return $this;
	}

	function getContent(){
		return $this->content;
	}

	function loadTemplate($template, $Result = array()){
		throw new \Gallant\Exceptions\HelperException('no implementation function loadTemplate');
	}

	function send($name = false){
		if(!$name) $name = $this->name;		
		$this->phpmailer->SetFrom($this->option->user, $this->name);
		
		try{
			$this->phpmailer->MsgHTML($this->content);
			return $this->phpmailer->Send();
		}catch(phpmailerException $e){
			return false;
		}	
	}
}