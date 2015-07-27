/**********************************************************************
 *    						ItemList.js
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
var List = function(data){
	for(var i in data)
		this[i] = data[i];
	this.node = $("#"+this.id);
	this.wrapper = this.node.parent();
	
	this.initControls();
};

/**** Display a list ****/
List.prototype.display = function(force){	
	var data = {
		lines : this.lines,
		page : this.page,		
	};

	data.searches = JSON.stringify(this.searches);
	data.sorts = JSON.stringify(this.sorts);
	
	if(force){
		if((typeof(force) == "array" && !force.length) || (typeof(force) == "object" && !Object.keys(force).length))
			force = "";
		data["set-"+this.id] = force;	
	}
	
	var get = {
		refresh : 1
	}
	if(this.selected){
		get.selected = this.selected;
	};

    $.ajax({
        async : false,
        url: this.action + Object.toQueryString(get) ,
        type: 'post',
        data: data,
        cache : false,
    })
    .done(function(response){            
    	var node = this.target ? $(this.target) : this.wrapper;
        node.html(response);
    }.bind(this))

    .fail(function(xhr, status, error){
    	app.notify("error", Lang.get("main.refresh-list-error"));
    });

    return false;
};

List.prototype.refresh = function(){
	var force = $("#"+this.id).find(".list-forced-result");
	if(force.length)
		return this.display($.evalJSON(force.val()));
	else
		return this.display();		
};

List.prototype.set = function(data){
	for(var i in data)
		this[i] = data[i];
	this.refresh();
};

List.prototype.changeMaxLines = function(lines){
	this.set({lines : lines});
};

List.prototype.goToPage = function(page){
	this.set({page : page});
};

List.prototype.sort = function(field, order){
	if (order == '') {
		delete(this.sorts[field]);
	}
	else{
		this.sorts[field] = order;
	}
	
	this.set({sorts : this.sorts});	
};

List.prototype.search = function(field, search){
	this.searches[field] = search;
			
	this.set({searches: this.searches});
};

List.prototype.initControls = function(){
	var self = this;
	
	/**
	 * Select all the lines
	 * */
	this.node.find(".list-checkbox-all").change(function(){
		self.node.find(".list-checkbox").prop('checked',$(this).is(":checked")).trigger("change");
	});
		
	/**
	 * Select a line
	 * **/
	this.node.find('.list-checkbox').change(function(){
		self.node.find(".list-checkbox-all").prop("checked", self.node.find(".list-checkbox:not(:checked)").length === 0);
	});
	
	/**
	 * Change the number of lines per page
	 * */
	this.node.find('.list-max-lines').change(function(){
		self.changeMaxLines($(this).val());
	});
		
	/**
	 * Go to the page xx
	 * */
	this.node.find('.list-page-number').change(function(){
		var page = $(this).val();
		if (isNaN(page)) {
			page = 1;
		}
		if (page < 1) {
			page = 1;
		}
		if (page > self.maxPages) {
			page = self.maxPages;
		}
		self.set({page : page});
	});
	
	/** 
	 * Go to the previous page
	 * */
	this.node.find('.list-previous-page').click(function(){
		self.node.find('.list-page-number').val(self.page - 1).trigger('change');
	});
	
	/**
	 * Go to the next page
	 * */
	this.node.find('.list-next-page').click(function(){
		self.node.find('.list-page-number').val(self.page + 1).trigger('change');
	});
	
	/**
	 * Sort the list
	 * */
	this.node.find('.list-sort-column').click(function(){
		var field = $(this).data('field');
		var value = $(this).attr('value');
		self.sort(field, value);
	});
	 
	/**
	 * Type a search
	 * */
	this.node.find('.list-search-input').change(function(){
		/*** 
			When a user types a research, he has 400ms left to send the request, 
			to avoid multiple request for one research
		***/
		var field = $(this).data('field');
		var value = $(this).val().trim();
		self.search(field, value);
	});

	/**
	 * Clean a search
	 */
	this.node.find(".clean-search").click(function(){
		var field = $(this).data('field');
		self.search(field, '');
	})
	
	this.node.find('.list-line').dblclick(function(){
		$(this).find(".list-cell-clickable").first().trigger('click');		
	});	
};



/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/