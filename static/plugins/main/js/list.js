define('list', ['jquery', 'ko'], function($, ko){
    /** 
     * This class describe the client behavior of the item lists
     */
    var List = function(data){
        this.id = data.id;
        this.action = data.action;
        this.target = data.target;
        this.maxPages = ko.observable();

        this.node = $("#"+this.id);
        this.wrapper = this.node.parent();
        this.refreshContainer = this.node.find(".list > tbody");

        // Get the list display parameters (number of lines, page number, searches and sorts)
        var params = JSON.parse($.cookie('list-' + this.id)) || {};

        this.searches = params.searches || {};
        this.sorts = params.sorts || {};
        this.page = ko.observable(params.page || 1);
        this.lines = ko.observable(params.lines || 20);

        this.fields = {};
        for(var j = 0; j < data.fields.length; j++){
            var field = data.fields[j];
            this.fields[field] = {
                name : field,
                search : ko.observable(this.searches[field]),
                sort : ko.observable(this.sorts[field])
            }
        }

        this.initControls();
    };


    /**
     * Refresh the list
     */
    List.prototype.refresh = function(){
        var data = {
            lines : this.lines(),
            page : this.page(),       
            searches : this.searches,
            sorts : this.sorts
        };

        $.cookie('list-' + this.id, JSON.stringify(data), {expires : 365, path : '/'});
        
        var get = {
            refresh : 1
        }
        
        if(this.selected){
            get.selected = this.selected;
        };

        $.ajax({
            async : false,
            url: this.action,
            method : 'GET',
            data : get,
            cache : false,
        })
        .done(function(response){    
            this.refreshContainer.html(response);
        }.bind(this))

        .fail(function(xhr, status, error){
            app.notify("error", Lang.get("main.refresh-list-error"));
        });

        return false;
    };


    /**
     * Listen for list parameters changements to refresh the list
     */
    List.prototype.initControls = function(){
        var self = this;
        
        /**
         * Select all the lines
         */
        this.node.find(".list-checkbox-all").change(function(){
            self.node.find(".list-checkbox").prop('checked',$(this).is(":checked")).trigger("change");
        });
            
        /**
         * Select a line
         */
        this.node.find('.list-checkbox').change(function(){
            self.node.find(".list-checkbox-all").prop("checked", self.node.find(".list-checkbox:not(:checked)").length === 0);
        });
        

        /**
         * Change the number of lines per page
         */
        this.lines.subscribe(function(value){
            this.refresh();
        }.bind(this));
        
            
        /**
         * Go to the page xx
         */
        this.page.subscribe(function(value){
            if (isNaN(value)) {
                this.page(1);
                return;
            }

            if (value < 1) {
                this.page(1);
                return;
            }

            if (value > this.maxPages()) {
                this.page(this.maxPages());
                return;
            }

            this.refresh();
        }.bind(this));


        /**
         * Detect, when the max page number changed, to keep the page number lower than it
         */
        this.maxPages.subscribe(function(value){
            if (this.page() > value) {
                this.page(value);
            }
        }.bind(this));

       
        $.each(this.fields, function(name, field){
            /**
             * Sort the list
             */
            field.sort.subscribe(function(value){
                if (value == '') {
                    delete(this.sorts[name]);
                }
                else{
                    this.sorts[name] = value;
                }

                this.refresh();
            }.bind(this));


            /**
             * Type a search
             */
            field.search.subscribe(function(value){
                if(value){
                    this.searches[name] = value;
                }
                else{
                    delete(this.searches[name]);
                }
                
                // Wait for 400 ms to refresh the list, in case the user enter new characters in this interval
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(function(){
                    this.refresh();
                }.bind(this), 400);

            }.bind(this));
        }.bind(this));      
    };

    return List;
});