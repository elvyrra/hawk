/**********************************************************************
 *    						utils.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
/*______________________________________________________________________________

	This file gives some usefull functions and methods
______________________________________________________________________________*/
var util = {
	loadedScripts : new Array(),
	
	/*______________________________________________________________________________

			Load a Javascript file dynamically
	______________________________________________________________________________*/
	require : function(script, visible){
		$.ajax({
			url : script,
			dataType : "script",
			async : false,
			success :function(code, status, xhr){				
				if(visible){
					var s = document.createElement("script");
					s.type = "text/javascript";
					s.src = script;												
					document.getElementsByTagName("head")[0].appendChild(s);
				}
				util.loadedScripts.push(script);
			},
			error: function(xhr, textStatus, errorThrown) {							
				throw errorThrown;
			}
		});	
	},

	/*______________________________________________________________________________

			Load a Javascript file dynamically, only if not already loaded
	______________________________________________________________________________*/
	require_once : function(script, visible){
		if(this.loadedScripts.indexOf(script) == -1)
			this.require(script, visible);
	},
	
	fa2png : function(icon, color, size){
		// create a temporary icon
		$("body").append("<i class='fa2png fa fa-"+icon+"'></i>");
		
		// get the text code of the icon
		var text = window.getComputedStyle($(".fa2png").last()[0], ':before').content;
		
		// create a canvas to draw the icon on it
		var canvas = document.createElement('canvas');		
		var context = canvas.getContext("2d");
		canvas.width = size + 3;
		canvas.height = size + 3;
		context.fillStyle = color;
		context.font = size+"px FontAwesome";
		context.fillText(text, 3, 13);
	  
		// get the data of the canvas as png
		var url = canvas.toDataURL('image/png');
		
		// remove the temporary icon
		$(".fa2png").last().remove();
		
		// return the icon as png
		return url;
	}
	
};

Number.prototype.format = function(decimals, decimalSeparator){	
	var output = this.toFixed(decimals);
	output = output.replace(".",decimalSeparator);
	return output;
};

Function.prototype.extend = function(parent){
	var child = this;
	child.prototype = parent;
	child.prototype.$super = parent;
	child.prototype = new child(Array.prototype.slice.call(arguments,1));
	child.prototype.constructor = child;
};

Object.getKeyByValue= function(obj, value ) {
    for( var prop in obj ) {
        if( obj[ prop ] === value ){
            return prop;
        }
    }
	return null;
};


window.reload = function(){
	location = location.href;
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/