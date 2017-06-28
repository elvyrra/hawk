/* global app, Lang, moment */

'use strict';

define('form', ['jquery'], function($) {
    /**
     * Class FormInput, represents any input in a form
     *
     */
    class FormInput {
        /**
         * Constructor
         * @param {Object} field The input parameters
         * @param {Form} form The form the input is asssociated with
         **/
        constructor(field, form) {
            this.form = form;
            for (var key in field) {
                if (field.hasOwnProperty(key)) {
                    this[key] = field[key];
                }
            }

            // this.node = $('[id=\'' + this.id + '\']');

            if (this.type === 'submit') {
                this.node().click(() => {
                    // Ask for confirmation
                    if (this.name === 'delete' && !confirm(Lang.get('form.confirm-delete'))) {
                        // The user finally doesn't want to delete the record
                        return false;
                    }

                    // The user confirmed
                    this.form.setObjectAction(this.name);

                    return true;
                });
            }
        }

        /**
         * Get the node representing the input
         * @returns {DOMNode} The node representing the input
         */
        node() {
            return $('[id=\'' + this.id + '\']');
        }


        /**
         * Get or set the value of the field
         *
         * @param {string} value If this variable is set, it will be set to the input
         * @returns {string} The input value or the value that has been set
         */
        val(value) {
            const node = this.node();

            if (value === undefined) {
                // Get the input value
                switch (this.type) {
                    case 'checkbox' :
                        return node.prop('checked');

                    case 'radio' :
                        return node.find(':checked').val();

                    case 'html' :
                        return node.html();

                    default :
                        return node.val();
                }
            }
            else {
                switch (this.type) {
                    case 'checkbox' :
                        node.prop('checked', value);
                        break;

                    case 'radio' :
                        node.find('[value="' + value + '"]').prop('checked', true);
                        break;

                    case 'html' :
                        node.html(value);
                        break;

                    default :
                        node.val(value);
                        break;
                }
            }

            return value;
        }


        /**
         * Get a property data of the field
         *
         * @param {string} prop - the property to get the data value
         * @returns {styring} The value of the property
         */
        data(prop) {
            return this.node().data(prop);
        }


        /**
         * Check the value of the field is valid
         *
         * @returns {bool} True if the field is valid, false else
         */
        isValid() {
            var value = this.val();

            // If the field is required, the field can't be empty
            if (this.required) {
                var emptyValue = this.emptyValue || '';

                if (value.toString() === emptyValue.toString()) {
                    this.addError(Lang.get('form.required-field'));
                    return false;
                }
            }

            // If the field has a specific pattern, test the value with this pattern
            var patternError = false;

            if(value) {
                if(this.node().hasClass('datetime')) {
                    let pattern = this.pattern;

                    value = moment(value, pattern).format('YYYY-MM-DD');

                    if(value === 'Invalid date') {
                        patternError = true;
                    }
                }
                else if (this.pattern) {
                    var regex = new RegExp(this.pattern.substr(1, -1));

                    if (!regex.test(value)) {
                        patternError = true;
                    }
                }
            }

            if(patternError) {
                this.addError(Lang.exists('form.' + this.type + '-format') ?
                    Lang.get('form.' + this.type + '-format') :
                    Lang.get('form.field-format')
                );
                return false;
            }

            if (this.minimum) {
                if (value && value < this.minimum) {
                    this.addError(Lang.get('form.number-minimum', {value: this.minimum}));
                    return false;
                }
            }

            if (this.maximum) {
                if (value && value > this.maximum) {
                    this.addError(Lang.get('form.number-maximum', {value: this.maximum}));
                    return false;
                }
            }

            // If the field has to be compared with another one, compare the two values
            if (this.compare) {
                if (value !== this.form.inputs[this.compare].val()) {
                    this.addError(Lang.get('form.' + this.type + '-comparison'));
                    return false;
                }
            }

            return true;
        }


        /**
         * Display an error on the input
         *
         * @param {string} text The error message to set to the input
         */
        addError(text) {
            if (this.errorAt) {
                this.form.inputs[this.errorAt].addError(text);
            }
            else {
                this.node().addClass('error').after('<span class="input-error-message">' + text + '</span>');
            }
        }

        /**
         * Remove the errors on the input
         */
        removeError() {
            this.node().removeClass('error').next('.input-error-message').remove();
        }
    }





    /**
     * This class is used to validate and submit forms client side.
     * forms are accessible to window by app.formrs[id]
     */
    class Form {
        /**
         * Constructor
         * @param {string} id - the id of the form
         * @param {Object} fields - The list of all fields in the form
         */
        constructor(id, fields) {
            this.id = id;
            this.node = $('[id=\'' + this.id + '\']');
            this.upload = this.node.hasClass('upload-form');
            this.action = this.node.attr('action');
            this.method = this.node.attr('method').toLowerCase();
            this.fields = fields;
            this.inputs = {};

            for (var name in fields) {
                if (fields.hasOwnProperty(name)) {
                    this.inputs[name] = new FormInput(fields[name], this);
                }
            }

            // Listen for form submission
            this.node.get(0).onsubmit = () => {
                this.submit();

                return false;
            };

            // Listen for form change
            this.onchange = null;
            this.node.change((event) => {
                if (this.onchange) {
                    this.onchange.call(this, event);
                }
            });
        }

        /**
         * Check the dat of the form
         *
         * @returns {boolean} True if the form data is correct, false else
         */
        isValid() {
            var valid = true;

            this.removeErrors();
            for (var name in this.inputs) {
                if (!this.inputs[name].isValid()) {
                    valid = false;
                }
            }

            return valid;
        }


        /**
         * Remove all the form errors
         *
         */
        removeErrors() {
            this.node.find('.form-result-message').removeClass('alert alert-danger').text('');
            for (var name in this.inputs) {
                if (this.inputs.hasOwnProperty(name)) {
                    this.inputs[name].removeError();
                }
            }
        }


        /**
         * Display an error message to the form
         *
         * @param  {string} text The message to display
         */
        displayErrorMessage(text) {
            this.node.find('.form-result-message')
                .addClass('alert alert-danger')
                .html('<i class=\'icon icon-exclamation-circle\'></i>  ' + text);
        }


        /**
         * Display the errors on the form inputs
         *
         * @param  {Object} errors The errors to display, where keys are inputs names, and values the error messages
         */
        displayErrors(errors) {
            if (typeof errors === 'object' && !(errors instanceof Array)) {
                for (var id in errors) {
                    if (errors.hasOwnProperty(id)) {
                        this.inputs[id].addError(errors[id]);
                    }
                }
            }
        }


        /**
         * Set the object action of the form. The object action can be "register" or "delete",
         * and represents the action that will be performed server side
         *
         * @param {string} action - The action value to set
         */
        setObjectAction(action) {
            $(this.node).find('[name="_submittedForm"]').val(action);

            if (action.toLowerCase() === 'delete') {
                this.method = action;
            }
        }


        /**
         * Submit the form
         *
         * @returns {boolean} False
         */
        submit() {
            // Remove all Errors on this form
            this.removeErrors();

            if (this.objectAction === 'delete' || this.isValid()) {
                app.loading.start();

                // Send an Ajax request to submit the form
                var data;

                if (this.method === 'get' || this.method === 'delete') {
                    data = $(this.node).serialize();
                }
                else {
                    data = new FormData(this.node.get(0));
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

                $.ajax(options)

                .done((results) => {
                    // treat the response
                    if (results.message) {
                        app.notify('success', results.message);
                    }

                    // Trigger a form_success event to the form
                    if (this.onsuccess) {
                        this.onsuccess(results.data);
                    }
                })

                .fail((xhr) => {
                    if (!xhr.responseJSON) {
                        // The returned result is not a JSON
                        this.displayErrorMessage(xhr.responseText);
                    }
                    else {
                        var response = xhr.responseJSON;

                        switch (xhr.status) {
                            case 400 :
                                // The form has not been checked correctly
                                this.displayErrorMessage(response.message);
                                this.displayErrors(response.details);
                                break;

                            case 500 :
                                // An error occured in the form treatment
                                this.displayErrorMessage(response.message);
                                break;

                            default :
                                this.displayErrorMessage(Lang.get('main.technical-error'));
                                break;
                        }

                        if (this.onerror) {
                            this.onerror(response.data);
                        }
                    }
                })

                .always(function() {
                    app.loading.stop();
                });
            }
            else {
                this.displayErrorMessage(Lang.get('form.error-fill'));
            }

            return false;
        }


        /**
         * Reset the form values
         */
        reset() {
            this.node.get(0).reset();
        }


        /**
         * Get the form data as Object
         *
         * @memberOf Form
         * @returns {Object} The object containing the form inputs data
         */
        valueOf() {
            var result = {};

            for (var name in this.inputs) {
                if (this.inputs.hasOwnProperty(name)) {
                    var item = this.inputs[name],
                        matches = (/^(.+?)((?:\[(.*?)\])+)$/).exec(name);

                    if (matches !== null) {
                        var params = matches[2];

                        if (!result[matches[1]]) {
                            result[matches[1]] = {};
                        }

                        var tmp = result[matches[1]],
                            m;

                        do {
                            m = (/^(\[(.*?)\])(\[(.*?)\])?/).exec(params);

                            if (m !== null) {
                                if (m[3]) {
                                    if (!tmp[m[2]]) {
                                        tmp[m[2]] = m[4] ? {} : [];
                                    }
                                    tmp = tmp[m[2]];
                                    params = m[3];
                                }
                                else if (tmp instanceof Array) {
                                    tmp.push(item.val());
                                }
                                else {
                                    tmp[m[2]] = item.val();
                                }
                            }
                        } while (m && m[3]);
                    }
                    else {
                        result[name] = item.val();
                    }
                }
            }

            return result;
        }


        /**
         * Display the content of the form
         *
         * @memberOf Form
         * @returns {string} The JSON representing the form inputs data
         */
        toString() {
            return JSON.stringify(this.valueOf());
        }


        /**
         * Static method to test if a type is supported for in put by the browser
         * @param   {string} type The type to test
         * @returns {boolean}     True if the type is supported, else False
         */
        static checkInputTypeSupport(type) {
            var input = document.createElement('input');

            input.setAttribute('type', type);

            return input.type === type;
        }
    }






    return Form;
});