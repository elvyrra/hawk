/**********************************************************************
 *    						tabs.js
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
var Tab = function(id){
	this.id = ko.observable(id);
	this.title = ko.observable("");
	this.url = ko.observable("");
	this.content = ko.observable("");

	this.titleSelector = '#main-tab-title-' + this.id();
	this.paneSelector = '#main-tab-' + this.id();
	this.history = [];
	var self = this;
};

Tab.create = function(id){
	return new Tab(id);	
};

Tab.prototype.getPaneNode = function(){
	return $(this.paneSelector);
};

Tab.prototype.getContent = function(){
	return this.content();
};

Tab.prototype.setContent = function(html){
	this.content(html);	
};

Tab.prototype.getTitleNode = function(){
	return $(this.titleSelector);	
};

Tab.prototype.getTitle = function(){
	return this.title();
};

Tab.prototype.setTitle = function(title){
	this.title(title);
};

Tab.prototype.activate = function(){
	$('#main-tab-title-' + this.id() + ' a').tab('show');	
};

Tab.prototype.setUrl = function(url){
	this.getPaneNode().data('url', url);
	this.url = url;
};









var Tabset = function(){
	this.tabs = ko.observableArray([]);

	$("body").on("shown.bs.tab", ".main-tab-title", function(evt){
		var id = $(evt.currentTarget).attr('data-tab');
		var tab = this.tabs()[id];
		history.replaceState({}, "", "#!" + tab.history[tab.history.length - 1]);
	}.bind(this));
};

Tabset.MAX_TABS_NUMBER = 20;
Tabset.titlesContainer = "#main-nav-tabs";
Tabset.panesContainer = "#main-tab-content";
Tabset.index = 0;

Tabset.prototype.push = function(){
	if(this.tabs().length < Tabset.MAX_TABS_NUMBER){
		/* Create the tab */
		var index = this.tabs.push(new Tab(Tabset.index ++)) - 1;
		
		/*** Activate the new tab ***/
		this.activateTab(index);
		
	}		
	else{
		app.notify('info', Lang.get('main.all-tabs-open'));
	}	
};

Tabset.prototype.activateTab = function(id){
	this.tabs()[id].activate();	
};

Tabset.prototype.remove = function(id){
	if (this.getActiveTab() == this.tabs()[id]){
		var next = this.getNextTab(id);
		/* Activate the next tab */
		next.activate();
	}
	
	/* Delete the tab nodes */
	this.tabs.splice(id, 1);	

	/* Register the new list of tabs */
	this.registerTabs();
		
};

Tabset.prototype.getActiveTab = function(){
	var id = parseInt($('.main-tab-title.active').data('tab'));
	return this.tabs()[id];
};

Tabset.prototype.getNextTab = function(id){
	if (id == this.tabs().length - 1){
		// This tab is the last one, get the previous one
		return this.tabs()[id - 1];
	}
	else{
		return this.tabs()[id + 1];
	}	
};

Tabset.prototype.registerTabs = function(){	
	var data = [];
	for(var i = 0; i < this.tabs().length; i++){
		data.push(this.tabs()[i].url());
	}
	$.cookie('open-tabs', JSON.stringify(data), {expire : 365, path : '/'});
};

Tabset.prototype.clickTab = function(data, event){
	if(event.which === 2){
		var tabId = $(event.currentTarget).attr('data-tab');

		this.remove(tabId);

		return false;
	}

	else{
		return true;
	}
}