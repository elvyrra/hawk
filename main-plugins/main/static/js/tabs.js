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
	this.id = id;
	this.titleNode = $('#main-tab-title-' + this.id);
	this.paneNode = $('#main-tab-' + this.id);
	this.closeNode = $(".main-tabs-close[data-tab='" + this.id + "']");
	this.history = [];
	var self = this;

	this.titleNode.find("a").on("shown.bs.tab", function(){
		history.replaceState({}, "", "#!" + this.history[this.history.length - 1]);
	}.bind(this));
};

Tab.create = function(id){
	/*** Add the title ***/	
	$(Tabset.titlesContainer).find('.add-tab-button').before($("#tab-title-template").html().replace(/@@id/g, id));		
				
	/*** Add the panel ***/
	$(Tabset.panesContainer).append($("#tab-content-template").html().replace(/@@id/g, id));
	
	return new Tab(id);
};

Tab.prototype.getPaneNode = function(){
	return this.paneNode;
};

Tab.prototype.getContent = function(){
	return this.getPaneNode().html();
};

Tab.prototype.setContent = function(html){
	this.getPaneNode.html(html);	
};

Tab.prototype.getTitleNode = function(){
	return this.titleNode;	
};

Tab.prototype.getTitle = function(){
	return this.getTitleNode().find('a').html();
};

Tab.prototype.setTitle = function(title){
	this.getTitleNode().find('a').html(title);
};

Tab.prototype.activate = function(){
	this.titleNode.find('a').tab('show');	
};

Tab.prototype.remove = function(){
	this.titleNode.remove();
	this.paneNode.remove();	
};

Tab.prototype.setUrl = function(url){
	this.getPaneNode().data('url', url);
	this.url = url;
};









var Tabset = function(){
	this.tabs = {};
};

Tabset.MAX_TABS_NUMBER = 10;
Tabset.titlesContainer = "#main-nav-tabs";
Tabset.panesContainer = "#main-tab-content";

Tabset.prototype.push = function(){
	var indexes = Object.keys(this.tabs);
	if(this.getTabsNumber() < Tabset.MAX_TABS_NUMBER){
		var index = indexes.length ? indexes.max() + 1 : 0;
		
		/* Create the tab */
		this.tabs[index] = Tab.create(index);		
		
		/*** Activate the new tab ***/
		this.activateTab(index);
		
		// Display / hide close buttons
		this.displayControlButtons();
		
		// Add the tab to the tabs object		
		this.registerTabs();
	}		
	else{
		app.advert('info', Lang.get('main.all-tabs-open'));
	}	
};

Tabset.prototype.activateTab = function(id){
	this.tabs[id].activate();	
};

Tabset.prototype.remove = function(id){
	if (this.getActiveTab() == this.tabs[id]){
		var next = this.getNextTab(id);
		/* Activate the next tab */
		next.activate();
	}
	
	/* Delete the tab nodes */
	this.tabs[id].remove();
	/* remove the tab object from the "tabs" structure */
	delete(this.tabs[id]);
	/* Register the new list of tabs */
	this.registerTabs();
		
	this.displayControlButtons();
};

Tabset.prototype.displayControlButtons = function() {	
	if(this.getTabsNumber() == 1)
		$(".main-tabs-close").hide();
	else
		$(".main-tabs-close").show();
		
	if(this.getTabsNumber() >= Tabset.MAX_TABS_NUMBER)
		$(".add-tab-button").hide();
	else
		$(".add-tab-button").show();
};

Tabset.prototype.getActiveTab = function(){
	var id = parseInt($('.main-tab-title.active').data('tab'));
	return this.tabs[id];
};

Tabset.prototype.getNextTab = function(id){
	var indexes = Object.keys(this.tabs);
	var index = indexes.indexOf(''+id);
	if (index == indexes.length - 1){
		// This tab is the last one, get the previous one
		return this.tabs[indexes[index - 1]];
	}
	else{
		return this.tabs[indexes[index + 1]];
	}	
};

Tabset.prototype.registerTabs = function(){	
	var data = [];
	for(var i in this.tabs){
		data.push(this.tabs[i].url);
	}
	$.cookie('open-tabs', JSON.stringify(data), {expire : 365, path : '/'});
};

Tabset.prototype.getTabsNumber = function(){
	return Object.keys(this.tabs).length;
};
