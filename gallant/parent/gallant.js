
/**
* G
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

var Gallant = {
	init : function(){

	},

	
	link : function(url){
		
	}

}

Gallant.Ajax = function(url){
	this._url = url;

	this._param = {};

	this.param = function(param){
		this._param = param;
		return this;
	}
	this.send = function(callback){
		console.log(this._url, this._param);
		$.ajax({
			type: "POST",
			cache: false,
			url: this._url,
			data: this._param,
			dataType: 'json',
			success: function(msg){
	            console.log(msg);
				if(callback) callback(msg['result']);
			},
			error: function(e1, e2, e3){
				console.log(e1, e2, e3)
			}
		});
	}
}

Gallant.Lang = {
	_data : {},
	_lang : false,

	getWord : function(key){
		return 'LANG:' + this._lang + ':' + key;
	}
}