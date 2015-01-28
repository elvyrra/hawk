/**********************************************************************
 *    						page.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Mint's project.
 *
 *
 **********************************************************************/
var page = {
	lists : {},
	
	
	/**
	 * Load A module or a page into a page section or a whole tab
	 * @param String url, the url to laod
	 * @param Object data (optionnal), contains the options for the load :
	 * 		- "newtab", to load the url into a new tab
	 * 		- "selector", a CSS selector where to load the page
	 * 		- "post", to make a POST Http request
	 * 		- "callback", to execute a callback chen the page is loaded
	 **/
	load : function(url, data){
		/*** Default options ***/
		var options = {			
			newtab : false,
			callback : null,
			post : null
		};
		
		for(var i in data)
			options[i] = data[i];
			
		if(url){
			this.loading.start();			            
			/*** WE FIRST CHECK THAT PAGE DOES NOT ALREADY EXIST IN A TAB ***/
			if(url != Router.create('MainController.newTab')){				
    			$("#main-tab-content > .tab-pane").each(function(){
    				if($(this).attr("data-url") === url){
						/*** The page is already open in another tab than the opened one ***/
						var index = $(this).attr('id').replace('main-tab-', '');
						tabset.activate(index);						
						options.newtab = false;
    				}			
    			});
			}			

            /*** A new tab has been asked ***/
            if(options.newtab){
                tabset.push();
            }
			if(!options.selector)
				options.selector = tabset.getActiveTabPane();
            
			/*** DETERMINE THE NODE THAT WILL BE LOADED THE PAGE ***/			
			if($(options.selector).length){
				$.ajax({
					url : url, 
					type : options.post ? 'post' : 'get',
					data : options.post,
					success : function(response){
						page.loading.stop();
						$(options.selector).html(response);
						if($(options.selector)[0] == tabset.getActiveTabPane()[0]){		
							// $(options.selector).height(window.screen.availHeight - 200);
							// $(options.selector).height($("html").height() - $(options.selector).offset().top - $("#footer").height() - 20);
							
							/***  The page has been loaded in a whole tab ****/							
							$(options.selector).attr("data-url", url);
							
							/*** Set the tab title ***/
							tabset.getActiveTabTitle().find('a').html($(options.selector).find(".page-name").first().val());							
						}

						if(options.callback){
					        /*** A callback has been asked ****/
							options.callback();
						}
					}, 
					error : function(xhr, status, error){
						page.advert("danger", Lang.get("main.loading-page-error"));
						page.loading.stop();
					},
				});
			}
			else{
		        /*** The selector to home the loaded url doesn't exist ***/
				page.loading.stop();
				this.advert("danger", Lang.get('main.loading-page-error'));
			}			
		}
		else{
			return false;
		}
	},
	
	
	/*_____________________________________________________________________________________
	
		Start and stop loading. Open a div wrapping all the div to avoid any user event
		when loading an action
	_____________________________________________________________________________________*/
	loading : {
		start : function(){
			if(!$("#loading").length)
				$("body").append("<div id='loading'><span class='fa fa-spinner fa-spin fa-5x'></span></div>");
			$('#loading').show();
		},
		
		stop : function(){
			$('#loading').hide();
		}
	},
	
	/*_____________________________________________________________________________________
	
		Display an advert into the page to display the current state
		@param : 
			o Object options :
				- string state ("error" | "highlight" | "active" | "default" | "focus"):
					define the graphic classname of the advert
				- string classname (optional): define the classname of the message (to fill it with
					a constant label)
				- string message (optional): define	the message to write.
				
		You have to choose between classname and message to define the message to write. For a 
		nominal functionality message, choose classname, and for an error return (for example
		a PHP error code), choose message.				
	_____________________________________________________________________________________*/
	advert : function(state, message){	
		var classname = "alert-"+state;
		
		$("#advert-message").remove();
		$('body').prepend(	"<div id='advert-message' class='alert "+classname+"' onclick='$(this).hide(\"slow\", function(){ $(this).remove() });'>"+								
								"<span>"+message+"</span>"+
								"<span class='close' onclick='$(this).parent().hide(\"slow\", function(){ $(this).remove() });'>&times</span>"+
							"</div>");		
		$("#advert-message").show("slow");
		if(state != "error"){
    		setTimeout(function(){
    			$("#advert-message .close").trigger("click");
    		}, 3500);
		}
	},
	
	
	/*__________________________________________________________________________
	
					LOAD A PAGE IN THE DIALOG BOX
		(required : a <div id='dialogbox'></div> in the page )
	__________________________________________________________________________*/
	dialog : function(data){
		var box = $("#dialogbox");
		if (!box.length) {
			console.error('The html-document template must contains the strcture of the modal window');
			return;
		}
		
		if(data == "close" || data.action=="close"){
			box.find('.modal-title, .modal-body').empty();
			box.modal('hide');
			return;
		}
		
		if(data.content){
			// Set the HTML content directly in the modal box
			box.find('.modal-body').html(data.content);
			box.find('.modal-title').html(data.title);
			box.modal('show');
		}
		else if(data.url){
			// Load the content from an url
			$.ajax({
				url : data.url,
				success : function(content){
					box.find('.modal-body').html(content);
					if (!data.title) {
						data.title = box.find('.modal-body .page-name').val() || '';
					}
					box.find('.modal-title').html(data.title);
					box.modal('show');	
				}
			});			
		}
	},
	
	/*__________________________________________________________________________
	
					ALERT A MESSAGE
	__________________________________________________________________________*/
	alert : function(){
		var message = arguments[0];
		var title, callback;
		for(var i = 1; i < arguments.length && i < 3; i++){
			switch(typeof(arguments[i])){
				case "string" :	title = arguments[i]; break;
				case "function" : callback = arguments[i]; break;
			}
		}
		
		this.dialog({
		    content : message,
		    title : title,
		    buttons : {
				OK : function(){					
					$("#dialogbox").dialog("close");
					if(callback)
						callback();
				}				
			}
		});	
	},
};

var Router = {
	create : function(method){
		var args = Array.prototype.slice.call(arguments, 1);
		for(var i = 0; i< this.routes.length; i++){
			var route = this.routes[i];
			if (route.action == method) {
				var url = route.originalUrl;
				for(var j= 0; j < args.length; j++){
					url = url.replace(/\{(\w+)\}/, args[j]);
				}
				return url;
			}
		}
		return '';
	}	
};

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/