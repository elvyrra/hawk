'use strict';

require(['app', 'emv', 'jquery'], (app, EMV, $) => {
    const form = app.forms['customize-list'];
    const allFields = JSON.parse(form.inputs.allFields.val());
    const displayedFields = JSON.parse(form.inputs.displayedFields.val());
    const immutableFields = JSON.parse(form.inputs.immutableFields.val());

    const model = new EMV({
        data : {
            allFields : allFields.map((field) => {
                const order = displayedFields.indexOf(field.name);

                if(order !== -1) {
                    field.displayed = true;
                    field.order = order;
                }
                else {
                    field.displayed = false;
                }

                if (immutableFields.indexOf(field.name) !== -1) {
                    field.displayed = true;
                    field.immutable = true;
                }

                return field;
            })
        },
        computed : {
            displayedFields : function() {
                return this.allFields

                .filter((field) => {
                    return field.displayed;
                })

                .sort((field1, field2) => {
                    return field1.order - field2.order;
                })

                .map((field) => {
                    return field.name;
                });
            },
            notDisplayedFields : function() {
                return this.allFields

                .filter((field) => {
                    return !field.displayed;
                });
            }
        }
    });


    model.displayedFilter = (field) => {
        return field.displayed;
    };

    model.notDisplayedFilter = (field) => {
        return !field.displayed;
    };

    model.refresh = function() {
        $(`#${form.id} .sortable`).sortable({
            // handle: '.drag-handle',
            placeholderClass : 'placeholder',
            group : EMV.utils.uid(),
            exclude : '.immutable',
            onDrop: ($item, container, _super) => {
                _super($item, container);

                // Get the moved item
                const movedItem = $item.get(0).$context;
                const list = container.target;

                // Get if the item has been moved in fields to display or not
                if(list.hasClass('displayed-fields')) {
                    // The field has been moved in the fields to display
                    list.children('li').each(function(index, elem) {
                        const item = elem.$context;

                        item.order = index;
                    });

                    movedItem.displayed = true;
                }
                else {
                    delete movedItem.order;
                    movedItem.displayed = false;
                }

                this.refresh();
            }
        });
    }.bind(model);

    model.$apply(form.node.get(0));
    model.refresh();
});