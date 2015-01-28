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
var tabset = {
    MAX_TABS_NUMBER : 10,
	
	push : function(url){
        if($("#main-nav-tabs > li[role='presentation']").length < this.MAX_TABS_NUMBER){
    	    /*** Find the index of this new tab ***/    		
			for(var i = 0; i < this.MAX_TABS_NUMBER; i++){
				if (! $("#main-tab-" + i).length) {
					index = i;					
					break;
				}
			}
    	    
			// In case of error, remove tabs with this id
			$("#main-tab-" + index + ", #main-tab-title-" + index).remove();
			
    	    /*** Add the title ***/
			$("#main-nav-tabs .add-tab-button").before(
				'<li role="presentation" class="main-tab-title corner-top" id="main-tab-title-' + i + '" data-tab="' + i + '">'+
					'<a href="#main-tab-' + i + '" aria-controls="main-tab-' + i + '" role="tab" data-toggle="tab"></a>' +
					'<i class="main-tabs-close fa fa-times-circle" data-tab="' + i +'"></i>'+
				'</li>'
			);
    	        	    
    	    /*** Add the panel ***/
			$("#main-tab-content").append('<div role="tabpanel" class="tab-pane main-tab-pane" id="main-tab-' + i + '" data-tab="' + i + '"></div>');
    	        	    
    	    /*** Activate the new tab ***/
    	    this.activate(i);
    	    
			// Display / hide close buttons
    	    this.displayControlButtons();
        }		
        else{
            page.advert('info', Lang.get('main.all-tabs-open'));
        }
	},
	
	activate : function(index){
		$('#main-nav-tabs a[href="#main-tab-' + index+ '"]').tab('show');
	},
	
	remove : function(index){
		// get the new tab to activate
		var next = $("#main-tab-"+index).next('.main-tab-pane')
		if (!next.length) {
			next = $("#main-tab-"+index).prev('.main-tab-pane')
		}
		var nextIndex = $(next).attr('id').replace("main-tab-", "");
		
		// remove the current tab
        $("#main-tab-" + index + ", #main-tab-title-" + index).remove();
        
		this.activate(nextIndex);
		
        this.displayControlButtons();
	},
	
	displayControlButtons : function(){
		if($(".main-tab-pane").length == 1)
			$(".main-tabs-close").hide();
		else
			$(".main-tabs-close").show();
		
		if($(".main-tab-pane").length >= this.MAX_TABS_NUMBER)
			$(".add-tab-button").hide();
	},
	
	getActiveTabIndex : function(){
		return $('.main-tab-title.active').data('tab');
	},
	
	getActiveTabTitle : function(){
		return $("#main-tab-title-" + this.getActiveTabIndex());
	},
	
	getActiveTabPane : function(){
		return $("#main-tab-" + this.getActiveTabIndex());
	}	
};



/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/