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

/**
 * @class Form
 * @constructs
 * @param {String} id - the id of the form
 * @param {Object} fields - The list of all fields in the form
 */
var Form = function(id, fields){	
	this.id = id;	
	this.node = $("[id='" + this.id + "']");
	this.upload = this.node.hasClass('upload-form');
	this.action = this.node.attr('action');
	this.method = this.node.attr('method');
	
	this.inputs = {};
	for(var name in fields){
		this.inputs[name] = new FormInput(fields[name], this);
	}
	
	var self = this;
	this.node.submit(function(event){
		/*** Prevent default browser behavior ***/
		event.preventDefault();		
		
		return self.submit();
	});
};



/**
 * Check the dat of the form
 * @return {bool} - true if the form data is correct, false else
 */
Form.prototype.check = function(){
	this.removeErrors();
	var valid = true;	
	for(var name in this.inputs){
		if (!this.inputs[name].check()) {
			valid = false;
		}
	}
	return valid;
};

Form.prototype.removeErrors = function(){
	this.node.find(".form-result-message").removeClass("alert alert-danger").text("");
	for(var name in this.inputs){
		this.inputs[name].removeError();
	}
};

Form.prototype.displayErrorMessage = function(text){
	this.node.find(".form-result-message").addClass("alert alert-danger").html("<i class='fa fa-exclamation-circle'></i>  " + text);
};

Form.prototype.displayErrors = function(errors){
	if (typeof errors === "object" && !(errors instanceof Array)) {
		for(var id in errors){		
			this.inputs[id].addError(errors[id]);
		}	
	}	
};


/**
 * Set the activity of the form. The activity can be "register" or "delete", 
 * and represents the action that will be performed server side
 * @param {string} activity - The activity value to set
 */
Form.prototype.setActivity = function(activity){
	this.activity = activity;

	if(self.name === "delete"){
		this.node.attr('method', activity);
	}
};


/**
 * Submit the form
 */
Form.prototype.submit = function(){		
	/*** Remove all Errors on this form ***/
	this.removeErrors();
	var self = this;
	
	if(this.activity == "delete" || this.check()){		
		mint.loading.start();
		
		/**** Send a POST Ajax request to submit the form ***/
		var data = this.node.serializeObject();
		if(this.activity){
			data['_FORM_ACTION_'] = this.activity;
		}

		
		$.ajax({
			xhr : mint.xhr,
			url : this.action,
			type : this.method,
			dataType : 'json',
			data : this.upload ? new FormData(this.node.get(0)) : data, /*** Send all the data contained in the form ***/
			processData : ! this.upload
		})
		.done(function(results, code, xhr){
			/*** treat the response ***/
			if(results.message){
				mint.advert("success", results.message);
			}
				
			/*** Trigger a form_success event to the form ***/
			self.node.trigger("success", results.data);
		})
		
		.fail(function(xhr, code, err){			
			if(! xhr.responseJSON){
				self.displayErrorMessage(xhr.responseText);
			}
			else{
				var response = xhr.responseJSON;
				switch(xhr.status){
					case 412 :
						// The form has not been checked correctly
						self.displayErrorMessage(response.message);
						self.displayErrors(response.errors);
					
						/*** Trigger a form_error event to the form ***/		
						self.node.trigger("error", response.data);
						break;
						
					case 424 :
						// An error occured in the form treatment						
						self.displayErrorMessage(response.message);
						
						/*** Trigger a form_error event to the form ***/		
						self.node.trigger("error", response.data);
						break;
						
					default :
						self.displayErrorMessage(Lang.get('main.technical-error'));
						
						/*** Trigger a form_error event to the form ***/		
						self.node.trigger("error", response.data);
						break;
				}
			}
		})
		.always(function(){
			mint.loading.stop();			
		});	
	}
	else{
		self.displayErrorMessage(Lang.get('form.error-fill'));
	}		
	return false;	
};


/**
 * Reset the form values 
 */
Form.prototype.reset = function(){
	this.node.get(0).reset();
}


/*----------------------- CLASS FormInput ------------------------*/

/**
 * Class FormInput, represents any input in a form
 * */
var FormInput= function(field, form){
	this.form = form;
	for(var key in field){
		this[key] = field[key];
	}
	this.node = $("[id='"+this.id+"']");
	var self = this;
	if (this.type == "submit") {		
		this.node.click(function(){			
			/*** Ask for confirmation ***/
			if(self.name == "delete" && !confirm(Lang.get("form.confirm-delete")))
				/*** The user finally doesn't want to delete the record ***/
				return false;	
			
			/*** The user confirmed ***/			
			self.form.setActivity(self.name);
		});
	}
};


/**
 * Get the value of the field
 */
FormInput.prototype.val = function(){
	return this.node.val();
};


/**
 * Get a property data of the field 
 * @param {string} prop - the property to get the data value
 */
FormInput.prototype.data = function(prop){
	return this.node.data(prop);
};


/**
 * Check the value of the field is valid
 */
FormInput.prototype.check = function(){
	/*** 1. If the field is required, the field can't be empty ***/
	if (this.required) {
		var emptyValue = this.emptyValue || '';
		if(this.val() == emptyValue){
			this.addError(Lang.get("form.required-field"));			
			return false;
		}
	}
	
	/*** 2. If the field has a specific pattern, test the value with this pattern ***/
	if(this.pattern){		
		var regex = eval(this.pattern);
		if(this.val() && ! regex.test(this.val())){
			this.addError(Lang.exists('form.' + this.type + "-format") ? Lang.get('form.'+ this.type + "-format") : Lang.get("form.field-format"));			
			return false;
		}
	}
	
	if (this.min) {
		if (this.val() && this.val() < this.min){
			this.addError(Lang.get('form.number-minimum', {value: this.min}));
			return false;
		}
	}
	
	if (this.max) {
		if (this.val() && this.val() > this.max){
			this.addError(Lang.get('form.number-maximum', {value: this.max}));
			return false;
		}
	}
		
	/*** 4. If the field has to be compared with another one, compare the two values ***/
	if(this.compare){
		if(this.val() != this.form.inputs[this.compare].val()){
			this.addError(Lang.get('form.'+ this.type + "-comparison"));			
			return false;
		}				
	}
	
	return true;
};


/**
 * Display an error on the field
 */
FormInput.prototype.addError = function(text){	
	if(this.errorAt){
		this.form.inputs[this.errorAt].addError(text);
	}
	else{
		this.node.tooltip('destroy');
		this.node.addClass('alert-danger').tooltip({
			title : text,
			placement: 'right',
			template: 	'<div class="tooltip input-error" role="tooltip">'+
							'<div class="tooltip-arrow"></div>'+
							'<div class="tooltip-inner"></div>'+
						'</div>'
		});
	}			
};

/** 
 * Remove the errors on the field 
 */
FormInput.prototype.removeError = function(){
	this.node.removeClass("alert-danger").tooltip("destroy");
};
