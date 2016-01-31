define('form', ['jquery', 'ko'], function($, ko){
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
		this.method = this.node.attr('method').toLowerCase();
		this.inputs = {};
		for(var name in fields){
			this.inputs[name] = new FormInput(fields[name], this);
		}
		
		// Listen for form submission
		this.node.submit(function(event){
			this.submit();

			return false;
		}.bind(this));

		// Listen for form change
		this.onchange = null;
		this.node.change(function(event){
			if(this.onchange){
				this.onchange.call(this, event);
			}
		}.bind(this));
	};



	/**
	 * Check the dat of the form
	 * @return {bool} - true if the form data is correct, false else
	 */
	Form.prototype.isValid = function(){
		this.removeErrors();
		var valid = true;	
		for(var name in this.inputs){
			if (!this.inputs[name].isValid()) {
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
		this.node.find(".form-result-message").addClass("alert alert-danger").html("<i class='icon icon-exclamation-circle'></i>  " + text);
	};

	Form.prototype.displayErrors = function(errors){
		if (typeof errors === "object" && !(errors instanceof Array)) {
			for(var id in errors){		
				this.inputs[id].addError(errors[id]);
			}	
		}	
	};


	/**
	 * Set the object action of the form. The object action can be "register" or "delete", 
	 * and represents the action that will be performed server side
	 * @param {string} action - The action value to set
	 */
	Form.prototype.setObjectAction = function(action){
		if(action.toLowerCase() === 'delete'){			
			this.method = action;
		}
	};


	/**
	 * Submit the form
	 */
	Form.prototype.submit = function(){		
		/*** Remove all Errors on this form ***/
		this.removeErrors();
		var self = this;
		
		if(this.objectAction == "delete" || this.isValid()){		
			app.loading.start();
			
			/**** Send a POST Ajax request to submit the form ***/
			var data;
			if(this.method === 'get'){
				data = $(this.node).serlialize();
			}
			else{
				data = new FormData(this.node.get(0))
			}			

			var options = {
				xhr : app.xhr,
				url : this.action,
				type : this.method,
				dataType : 'json',
				data : data,
				processData : false,
				contentType : false
			};			
			
			$.ajax(	options )

			.done(function(results, code, xhr){
				/*** treat the response ***/
				if(results.message){
					app.notify("success", results.message);
				}
					
				/*** Trigger a form_success event to the form ***/
				if(self.onsuccess){
					self.onsuccess(results.data);
				}
			})
			
			.fail(function(xhr, code, err){			
				if(! xhr.responseJSON){
					// The returned result is not a JSON
					self.displayErrorMessage(xhr.responseText);
				}
				else{
					var response = xhr.responseJSON;
					switch(xhr.status){
						case 412 :
							// The form has not been checked correctly
							self.displayErrorMessage(response.message);
							self.displayErrors(response.errors);
							break;
							
						case 424 :
							// An error occured in the form treatment						
							self.displayErrorMessage(response.message);
							break;
							
						default :
							self.displayErrorMessage(Lang.get('main.technical-error'));
							break;
					}
					if(self.onerror){
						self.onerror(results.data);
					}
				}
			})
			.always(function(){
				app.loading.stop();			
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


	/**
	 * Get the form data as Object
	 */
	Form.prototype.valueOf = function(){
		var result = {};

		for(var name in this.inputs){
			var item = this.inputs[name];		
			var matches = /^(.+?)((?:\[(.*?)\])+)$/.exec(name);
			if(matches !== null){
				var params = matches[2];
				if(! result[matches[1]]){
					result[matches[1]] = {};
				}
				var tmp = result[matches[1]];
				do{
					var m = /^(\[(.*?)\])(\[(.*?)\])?/.exec(params);
					if(m !== null){
						if(m[3]){
							if(!tmp[m[2]]){
								tmp[m[2]] = m[4] ? {} : [];
							}
							tmp = tmp[m[2]];
							params = m[3];
						}
						else{
							if(tmp instanceof Array){
								tmp.push(item.val());
							}
							else{
								tmp[m[2]] = item.val();
							}							
						}						
					}
				} while(m && m[3]);
			}
			else{
				result[name] = item.val();
			}
		}
		
		return result;
	};


	/**
	 * Display the content of the form
	 */
	Form.prototype.toString = function(){
		return JSON.stringify(this.valueOf());
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

		if (this.type == "submit") {		
			this.node.click(function(){			
				// Ask for confirmation 
				if(this.name == "delete" && !confirm(Lang.get("form.confirm-delete"))){
					// The user finally doesn't want to delete the record
					return false;	
				}
				
				// The user confirmed
				this.form.setObjectAction(this.name);
			}.bind(this));		
		}
	};


	/**
	 * Get or set the value of the field
	 * @param {string} value The value to set	 
	 */
	FormInput.prototype.val = function(value){
		if(value === undefined){
			// Get the input value
			switch(this.type){
				case 'checkbox' :
					return this.node.prop('checked');
					
				case 'radio' :
					return this.node.find(':checked').val();

				case 'html' :
					return this.node.html();

				default :
					return this.node.val();		
			}
		}
		else{
			switch(this.type){
				case 'checkbox' :
					this.node.prop('checked', value);
					break;
					
				case 'radio' :
					this.node.find('[value="' + value + '"]').prop('checked', true);
					break;

				case 'html' :
					this.node.html(value);
					break;

				default :
					this.node.val(value);
					break;		
			}
			
		}
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
	FormInput.prototype.isValid = function(){
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
		
		if (this.minimum) {
			if (this.val() && this.val() < this.minimum){
				this.addError(Lang.get('form.number-minimum', {value: this.minimum}));
				return false;
			}
		}
		
		if (this.maximum) {
			if (this.val() && this.val() > this.maximum){
				this.addError(Lang.get('form.number-maximum', {value: this.maximum}));
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
			this.node.addClass('error').after('<span class="input-error-message">'+ text + '</span>');
		}			
	};

	/** 
	 * Remove the errors on the field 
	 */
	FormInput.prototype.removeError = function(){
		this.node.removeClass('error').next('.input-error-message').remove();
	};

	return Form;
});