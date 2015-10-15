/**
 * This file contains native classes extensions
 */


/**
 * Format a number with a given number of decimals, and a custom separator
 */
Number.prototype.format = function(decimals, decimalSeparator){	
	var output = this.toFixed(decimals);
	output = output.replace(".",decimalSeparator);
	return output;
};


/**
 * Inherit a class
 */
Function.prototype.extend = function(parent){
	var child = this;
	child.prototype = parent;
	child.prototype.$super = parent;
	child.prototype = new child(Array.prototype.slice.call(arguments,1));
	child.prototype.constructor = child;
};


/**
 * Search a key corresponding to a given value 
 */
Object.getKeyByValue = function(obj, value ) {
    for( var prop in obj ) {
        if( obj[ prop ] === value ){
            return prop;
        }
    }
	return null;
};


/**
 * Make a query string from an object
 */
Object.toQueryString = function(object){
	var params = [];
	for(var i in object){
		params.push(encodeURIComponent(i) + "=" + encodeURIComponent(object[i]));
	}

	return "?" + params.join("&");
};

/**
 * Get the max value of an array
 */
Array.prototype.max = function(){
	return Math.max.apply(null, this);
};

/**
 * Get the min value of an array
 */
Array.prototype.min = function(){
	return Math.max.apply(null, this);
};