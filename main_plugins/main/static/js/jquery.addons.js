/**********************************************************************
 *    						jquery.addons.js
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
(function($) {
	$.fn.selectText = function(){
		var doc = document
			, element = this[0]
			, range, selection
		;
		if (doc.body.createTextRange) {
			range = document.body.createTextRange();
			range.moveToElementText(element);
			range.select();
		} else if (window.getSelection) {
			selection = window.getSelection();        
			range = document.createRange();
			range.selectNodeContents(element);
			selection.removeAllRanges();
			selection.addRange(range);
		}
	};

	$.fn.serializeObject = function(){
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};
	
	$.fn.calculator = function(format){		
		$(this).change(function(){
			var expr = $(this).val();			
			try{
				/*** Calculate the value of the field ***/
				var value = eval(expr);
				
				/*** Check the bounds of th field ***/
				if($(this).attr("data-min") !== undefined && parseFloat(value) < parseFloat($(this).attr("data-min")))
					value = parseFloat($(this).attr("data-min"));
				
				if($(this).attr("data-max") !== undefined && parseFloat(value) > parseFloat($(this).attr("data-max")))
					value = parseFloat($(this).attr("data-max"));
			
				/*** Cast the value in the good format ***/
				if(format == "int")
					value = parseInt(value);
				else if(!isNaN(format))
					value = value.toFixed(format);
			}	
			catch(e){
				value = expr;
			}
			
			
			/*** Write the value in the input ***/
			$(this).val(value);		
		});	
	};
	
	$.fn.insertAt = function(str, pos){
		var current = $(this).val();
		if(pos == undefined){			
			pos = $(this)[0].selectionStart ;
		}
		$(this).val(current.substring(0, pos) + str + current.substring(pos));		
		$(this)[0].setSelectionRange(pos + str.length, pos + str.length);	
		$(this).focus();
	},
	
	$.fn.are = function(condition){		
		for(var i = 0; i < $(this).length; i++){
			if(!$($(this)[i]).is(condition))
				return false;
		}
		return true;	
	};	
})(jQuery);

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/