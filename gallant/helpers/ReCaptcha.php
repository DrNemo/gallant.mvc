<?
/**
* Gallant\Helpers\ReCaptcha хелпер для работы с https://www.google.com/recaptcha/
* предварительно в конфиге укажите приватный и публичный ключи recaptcha
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

namespace Gallant\Helpers;
use \G;
use \Gallant\Exceptions\HelperException;

G::includeComponent('recaptchalib.php');

class ReCaptcha{

	static private $_publicKey = false;

	static private $_privateKey = false;

	function init(){
		if(!self::$_publicKey){
			$conf = G::getConfig('recaptcha');
			if(!$conf['publicKey'] || !$conf['privateKey']){
				throw new HelperException('error config ReCaptcha');
			}
			self::$_publicKey = $conf['publicKey'];
			self::$_privateKey = $conf['privateKey'];
		}
	}

	function getPublicKey(){
		self::init();
		return self::$_publicKey;
	}

	function getPrivateKey(){
		self::init();
		return self::$_privateKey;
	}

	static function htmlForm(){
		return recaptcha_get_html(self::getPublicKey());
	}
}

