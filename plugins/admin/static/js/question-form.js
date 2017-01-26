'use strict';

require(['app', 'emv'], function(app, EMV) {
    const form = app.forms['profile-question-form'];
    const parameters = JSON.parse(form.inputs.parameters.val());

    const model = new EMV({
        data : {
            type : form.inputs.type.val(),
            required : parameters.required,
            readonly : parameters.readonly,
            options : parameters.options ? parameters.options.join('\n') : '',
            minDate : parameters.min,
            maxDate : parameters.max,
            roles :  parameters.roles
        },
        computed : {
            parameters : function() {
                return JSON.stringify({
                    required : this.required,
                    readonly : this.readonly,
                    options : this.options ? this.options.split('\n') : [],
                    min : this.minDate,
                    max : this.maxDate,
                    roles : this.roles
                });
            }
        }
    });

    model.$apply(form.node.get(0));
});