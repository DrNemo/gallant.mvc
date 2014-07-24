
/**
* Gallant.js helper
* 
* @package Gallant
* @copyright 2013 DrNemo
* @license http://www.opensource.org/licenses/mit-license.html MIT License
* @author DrNemo <drnemo@bk.ru>
* @version 1.0
*/

var Gallant = {
	ref : function(url){
		if(!url){
			document.location.href = document.location.href;
		}else{
			document.location.href = url;
		}
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
		var url = this._url
		console.log(url, this._param);
		$.ajax({
			type: "POST",
			cache: false,
			url: this._url,
			data: this._param,
			dataType: 'json',
			success: function(msg){
				console.log(url, msg);
				if(callback) callback(msg['result']);
			},
			error: function(e1, e2, e3){
				console.log(e1, e2, e3)
			}
		});
	}
}
