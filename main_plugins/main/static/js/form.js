/**********************************************************************
 *    						form.js
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
jQuery().ready(function() {
	/*_______________________________________________________________
		
		Submit a form .ajax-form
	_______________________________________________________________*/
    $('body').on("submit",".ajax-form",function(event){
		var uploadForm = $(this).hasClass('upload-form');
		
		/*** Prevent default browser behavior ***/
		event.preventDefault();		
		
		/*** Remove all Errors on this form ***/
		$(this).removeErrors();
		var debug = false;
		var form = $(this);		
		if(debug || $(this).find("[name='_FORM_ACTION_']").val() == "delete" || $(this).check()){		
			page.loading.start();			
			
			/**** Send a POST Ajax request to submit the form ***/
			$.ajax({
				url : $(this).attr('action'), 
				type : $(this).attr("method"),
				data : uploadForm ? new FormData(this) : $(this).serialize(), /*** Send all the data contained in the form ***/
				processData : ! uploadForm,
				success:function(response){
					/*** The server returned a response, treat it ! ****/
					try{
						/*** Normally the server returns a JSON structure, we parse it to 
							create a Javascript Object ***/
						var results = JSON.parse(response);						
					}
					catch(e){
						/*** The response is not a well-formed JSON ***/
						$(form).find(".form-result-message").addClass("alert-danger").text(response);
						return false;
					}
					
					/**** treat the response ***/
					switch(results.data.status){
						case 'success' :
							if(!results.data.nomessage)							
								page.advert("success", results.data.message);
						
							/*** Trigger a form_success event to the form ***/
							form.trigger("form_success", results.data);
						break;
						
						case 'error' :
							/*** An error occured while submission ***/
							if(!results.data.nomessage)
								$(form).find(".form-result-message").addClass("alert alert-error").html(results.data.message);
							$(form).displayErrors(results.errors);
							
							/*** Trigger a form_error event to the form ***/		
							$(form).trigger("form_error", results.data);								
							
						break;						
					}										
					
				},
				complete : function(){
					page.loading.stop();
				}
			});	
		}
		else{
			$(form).find(".form-result-message").addClass("alert-danger").text(Lang.get('form.error-fill'));
		}		
		return false;
	})
    
	
	/*_______________________________________________________________
		
		Submit pushing button
	_______________________________________________________________*/
    .on("click","[type='submit']",function(event){
		/*** Ask for confirmation ***/
		if($(this).hasClass("input-delete") && !confirm("Voulez-vous vraiment supprimer cet enregistrement ?"))
			/*** The user finally doesn't want to delete the record ***/
			return false;	
		
		/*** The user confirmed ***/
		$(this).parents(".form").find("input[name='_FORM_ACTION_']").val($(this).attr('name'));
		$(this).parents(".form").attr("novalidate","");
    })	
	
	/*_______________________________________________________________
		
		Autocomplete date inputs
	_______________________________________________________________*/
    .on('focusout','.form input.datetime',function(){
        var now = new Date();
		var datas = $(this).val().replace(/_/gi,"").split("/");
        var day = page.language == "FR" ? datas[0] : datas[1], 
			month = page.language == "FR" ? datas[1] : datas[0], 
			year = datas[2];

        /*** Format the data ***/
        if(day=="")
            return;
        if(day.length==1){
            day = "0"+day;
        }

        if(month=="")
            month = now.getMonth() +1;
        if(month.toString().length==1)
            month = "0"+month;

        if(year=="")
            year = now.getFullYear().toString();
        if(year.length==2)
            year = "20"+year;
		
		$(this).val(page.language == "FR" ? day+"/"+month+"/"+year : month+"/"+day+"/"+year);
    })          
});


(function($) {	

	/*________________________________________________________________
 
			Validation of a form in javaScript
	_________________________________________________________________*/	
	$.fn.check = function(){
		/*** This function is recusrive. For a form, it calls the same method for every input, select or textarea
		of this form.
		If the node is not a form, an input, a textarea or a select, it throws an error
		***/		
		var state = true; // variable which has the current state of the check
		var nodeName = 	$(this)[0].nodeName.toLowerCase();
		
		switch(nodeName){
			case "form" :
				$(this).find("input, textarea, select").each(function(){
					if(! $(this).check())
						state = false;
				});		
			break;
			
			case "input":
			case "textarea":
			case "select":
				/*** To validate a field, there are three criterias :
					1. If the field is required, the field can't be empty 
					2. If the field has a specific pattern, test the value with this pattern
					3. If the field has to be compared with another one, compare the two values
				***/
				
				/*** 1. If the field is required, the field can't be empty ***/
				if($(this).attr("required")){
					var emptyValue = $(this).attr("data-empty") ? $(this).attr("data-empty") : "";
					if($(this).val() == emptyValue){
						$(this).addError(Lang.get("form.required-field"));
						return false;
					}
				}	
				
				/*** 2. If the field has a specific pattern, test the value with this pattern ***/
				if($(this).attr("data-pattern")){
					if($(this).val() && ! (new RegExp($(this).attr("data-pattern")).test($(this).val()))){
						$(this).addError(Lang.exists('form.' + $(this).attr("data-type")+ "-format") ? Lang.get('form.'+$(this).attr("data-type")+ "-format") : Lang.get("form.field-format"));
						return false;
					}
				}
					
				/*** 3. If the field has to be compared with another one, compare the two values ***/
				if($(this).attr("data-compare")){
					if($(this).val() != $(this).parents('form').first().find("[name='"+$(this).attr("data-compare")+"']").val()){
						$(this).addError(Lang.get('form.'+$(this).attr("data-type")+ "-comparison"));
						return false;
					}				
				}
			break;
		}		
		return state;
	};
	
	$.fn.addError = function(text){
		if($(this).data("errorat")){
			$("#"+$(this).data("errorat")).addError(text);
		}		
		else{
			if($(this).length){
				if(!$(this).is("[title]"))
					$(this).attr("title", text);
				$(this)
				.addClass("form-input alert-error")	
				.tooltip({
					content : text,
					tooltipClass : "alert-error", 
					position : {
						my : "left bottom", 
						at : "right top"
					}
				});
			}
		}
	};
	
	$.fn.removeErrors = function(){
		var nodeName = $(this)[0].nodeName.toLowerCase()
		if (nodeName == 'form') {
			$(this).find(".form-input.alert-error").removeClass("alert-error").tooltip("destroy");
			$(this).find(".form-result-message").removeClass("alert-error").text("");	
		}
		else if($(this).hasClass('form-input alert-error')){
			$(this).removeClass("alert-error").tooltip("destroy");
		}
	};
	
	$.fn.displayErrors = function(errors){		
		for(var id in errors){						
			$(this).find("[name='"+id+"']").addError(errors[id]);		
		}		
	};
	
	$.fn.validate = function(test, message){
		var container = test.errorAt ? $(test.errorAt) : $(this);
		switch(test.type){
			case "required" : 
				if(! $(this).val()){
					if(!message)
						message = "Champ obligatoire";
					$(container).addError(message);
					return false;
				}
			break;
		
			case "pattern" :				
				var reg = test.pattern;
				if(! reg.test($(this).val())){
					$(container).addError(message);
					return false;
				}
			break;
			
			case "eval" :
				if(! eval(test.condition)){
					$(container).addError(message);
					return false;
				}
			break;
		}
		return true;
	};
})(jQuery);

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/