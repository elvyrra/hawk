/* global $, app, ko, Lang */

'use strict';

require(['app'], function() {
    $("#manage-themes-page")

    .on("click", ".select-theme", function(){
        if(confirm(Lang.get("admin.theme-update-reload-page-confirm"))){
        	$.get(app.getUri("select-theme", {name : $(this).data("theme")}), function(){
                location.reload();
        	});
        }
    })

    .on("click", ".delete-theme", function(){
    	if(confirm(Lang.get("admin.theme-delete-confirm"))){
    		$.get(app.getUri("delete-theme", {name : $(this).data("theme")}), function(){
    			app.load(app.getUri("available-themes"), {selector: "#admin-themes-select-tab"});
    		})
    	}
    })

    .on("change", "#custom-theme-form", function(){
        var name = $(this).attr('name');
    })

    .on("click", ".delete-theme-media", function(){
    	if(confirm(Lang.get("admin.theme-delete-media-confirm"))){
    		$.get(app.getUri("delete-theme-media", {filename : $(this).data('filename')}), function(){
    			app.load(app.getUri("theme-medias"), {selector : "#admin-themes-medias-tab"});
    		});
    	}
    })

    .on("focus", ".theme-media-url", function(){
        $(this).select();
    });


    /**
     * Search themes from the sidebar widget
     */
    app.forms["search-themes-form"].submit = function(){
        if(this.isValid()){
            var url = app.getUri('search-themes') + '?search=' + this.inputs.search.val();
            app.load(url);
        }
        else{
            this.displayErrorMessage(Lang.get('form.error-fill'));
        }
        return false;
    };

    /**
     * Download a plugin from the platform
     */
    $(".download-theme").click(function(){
        app.loading.start();

        $.get($(this).attr('href'))

        .success(function(response){
            app.load(app.tabset.activeTab().uri());
        })

        .error(function(xhr, status, error){
            app.loading.stop();
            app.notify('error', error.message);
        });

        return false;
    });

    /**
     * Update a theme from the platform
     */
    $(".update-theme").click(function(){
        app.loading.start();

        $.get(app.getUri('update-theme', {theme : $(this).data('theme')}))

        .success(function(response){
            app.load(app.tabset.activeTab().uri());
        })

        .error(function(xhr, status, error){
            app.loading.stop();
            app.notify('error', error.message);
        });

        return false;
    });

    /**
     * Customize the theme variables
     */
    setTimeout(function(){
        require(['less'], function(){
            var form = app.forms['custom-theme-form'];

            // The id of the style tag containing the compiled CSS
            var cssId = "less:custom-base-theme";

            /**
             * When the form has been successfully submitted, reload the page CSS
             */
            form.onsuccess = function(data){
                $("#theme-base-stylesheet").attr('href', data.href);
            };

            var model = {
                vars : {},
                /**
                 * Reset the custom form
                 */
                reset : function(){
                    for(var i in this.vars){
                        this.vars[i](less.options.initVars[i]);
                    }
                },

                /**
                 * Refresh the CSS when a form value changes
                 * @return {Promise}
                 */
                refresh : function(){
                    var values = form.valueOf();
                    delete values.compiled, values.reset, values.valid;

                    return less.modifyVars(values);
                },

                updateTimeout : 0
            };

            // Add the theme less file to lessjs
            setTimeout(function(){
                less.registerStylesheets();
                model.refresh();
            });


            for(var i in form.inputs) {
                if(i !== 'compiled') {
                    var input = form.inputs[i];
                    model.vars[input.name] = ko.observable(input.val());

                    // Update a theme variable
                    model.vars[input.name].subscribe(function(value){
                        clearTimeout(model.updateTimeout);

                        // Real time compilation of the theme
                        model.updateTimeout = setTimeout(function(){
                            model.refresh()
                            .then(function(){
                                form.inputs['compiled'].val(document.getElementById(cssId).innerText);
                            });
                        }.bind(this), 50);

                        if(this.type === "color"){
                            this.node.parent().colorpicker('setValue', value);
                        }
                    }.bind(input));
                }
            }

            ko.applyBindings(model, form.node.get(0));
        });
    });

    /***
     * Ace editor for Css editing tab
     */
    (function(){
        var model = {
            css : ko.observable(app.forms['theme-css-form'].inputs['css'].val()),
        };

        ko.applyBindings(model, $("#theme-css-form").get(0));

        app.forms['theme-css-form'].onsuccess = function(data){
        	$("#theme-custom-stylesheet").attr('href', data.href);
        };
    })();


    /**
     * Treat the menu sort
     */
    (function(){
        var activeNode = document.getElementById('sort-menu-active');
        var inactiveNode = document.getElementById('sort-menu-inactive');

        var MenuModel = function(){
            this.items = ko.observableArray(JSON.parse(app.forms['set-menus-form'].inputs['data'].val()));
            this.items.extend({ notify: 'always' });

            this.inactiveItems = ko.computed(function(){
                return ko.utils.arrayFilter(this.items(), function(item){
                    return ! item.active;
                })
            }.bind(this));

            this.activeItems =  ko.computed(function(){
                return ko.utils.arrayFilter(this.items(), function(item){
                    return item.active;
                })
            }.bind(this));

            this.sortedItems = ko.computed(function(){
                var result = this.getItemsByParent(0);
                for(var i = 0; i <  result.length; i++){
                    result[i].children = this.getItemsByParent(result[i].id);
                }

                return result;
            }.bind(this));

            this.templateClone = $("#sort-menu-template").clone();
        }

        MenuModel.prototype.getItemById = function(id){
            for(var i = 0; i < this.items().length; i++){
                if(this.items()[i].id == id){
                    return this.items()[i];
                }
            }
        };

        MenuModel.prototype.getItemsByParent = function(parentId){
            var children = [];
            for(var i = 0; i < this.activeItems().length; i++ ){
                if(this.activeItems()[i].parentId == parentId){
                    delete(this.activeItems()[i].children);
                    children.push(this.activeItems()[i]);
                }
            }

            return children.sort(function(item1, item2){
                return item1.order - item2.order;
            })
        };

        MenuModel.prototype.activateItem = function(item, event){
            item.active = 1;
            item.parentId = 0;
            item.order = this.getItemsByParent(0).length;

            this.refresh();
        };

        MenuModel.prototype.deactivateItem = function(item, event){
            item.active = 0;
            this.refresh();
        };

        MenuModel.prototype.editItem = function(item, event){
            app.dialog(app.getUri('edit-menu', {itemId : item.id}));
        }

        MenuModel.prototype.removeItem = function(item, event){
            $.get(app.getUri('delete-menu', {itemId : item.id}), function(){
                this.items.splice(this.items.indexOf(item), 1);

                this.refresh();
            }.bind(this));
        };

        MenuModel.prototype.refresh = function(){
        	this.items.valueHasMutated();

        	$("#sort-menu-template").remove();
            $("#sort-menu-wrapper").after(this.templateClone.clone());
            this.template = $("#sort-menu-template").get(0);

            ko.cleanNode(activeNode);
            ko.applyBindings(this, activeNode);

            $("#sort-menu-wrapper .sortable").sortable({
    	        handle: '.drag-handle',
    	        placeholderClass : 'placeholder',

    	        isValidTarget: function($item, container){
    	            var id = parseInt($item.attr('data-id'));
    	            var moved = model.getItemById(id);

    	            var parentId = parseInt(container.target.attr('data-parent'));
    	            if((! moved.action || (moved.children && moved.children.length)) && parentId != 0 ){
    	                return false;
    	            }
    	            return true;
    	        },

    	        onDrop: function ($item, container, _super, event) {
    	            _super($item, container);

    	            // Get the moved item
    	            var id = parseInt($item.attr('data-id'));
    	            var moved = model.getItemById(id);

                    // Get the parent item
                    var parentId = parseInt(container.target.attr('data-parent'));
                    var parent = model.getItemById(parentId);

                    // calculate the new order of the item
                    var index = $item.parent().children().index($item);
                    moved.order = index;

                    // Increment items that are ordered after this one
                    $item.parent().children().each(function(index){
                        var item = model.getItemById($(this).attr('data-id'));
                        item.order = index;
                    });

                    // Set the parent id to the item
                    moved.parentId = parentId;

    	            model.refresh();
    	        }
    	    });
        };


        var model = new MenuModel();
        model.refresh();
        ko.applyBindings(model, inactiveNode);


        // Manage when a new menu item is created
        app.forms['set-menus-form'].node.on('register-custom-item', function(event, data){
            debugger;
            var item = model.getItemById(data.id);
            if(!item){
                model.items.push(data);
                this.reset();
            }
            else{
                for(var prop in data){
                    item[prop] = data[prop];
                }
                app.dialog("close");
            }

            model.refresh();
        });

    })();
});