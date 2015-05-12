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

Object.toQueryString = function(object){
	var params = [];
	for(var i in object){
		params.push(i + "=" + encodeURIComponent(object[i]));
	}

	return "?" + params.join("&");
};

Array.prototype.max = function(){
	return Math.max.apply(null, this);
};

Array.prototype.min = function(){
	return Math.max.apply(null, this);
};

history.empty = function(){
	history.go(-history.length);
};

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/