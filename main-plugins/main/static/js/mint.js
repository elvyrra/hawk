/*********************************************************************
 *    						mint.js
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
var App = function(){
	this.language = '';
	this.rootUrl = '';
	this.jsBaseUrl = document.getElementById("main-js-script").src.split("/").slice(0, -1).join("/") + "/";
	this.isConnected = false;
	this.routes = [];
	this.forms = {};
	this.lists = {};
	this.scripts = {};
	this.tabs = {};
	this.readyCallbacks = [];	

	this.init();
};


/**
 * Load a JavaScript file into the DOM
 * @param {Array} scripts - the list of the scripts to load
 * @param {Function} callback - The code to execute when all scripts are loaded
 */
App.prototype.require = function(scripts, callback){
	var remaining = scripts.length;
	if(typeof scripts === "string"){
		throw new Error("min.require expects the first argument to be array, string given");
	}
	if(! scripts.length){
		return callback();
	}
	
	var src = scripts.shift();
	if(! /^https?\:\/\//.test(src) && ! /^\//.test(src) ){
		src = this.jsBaseUrl + src;
	}

	if(! this.scripts[src]){
		var s = document.createElement("script");
		s.type = "text/javascript";
		s.src = src;		
		s.onload = function(){ 
			this.scripts[src] = 1;
			this.require(scripts, callback);
		}.bind(this);
		document.getElementsByTagName("head")[0].appendChild(s);
	}
	else{		
		this.require(scripts, callback);
	}
};


/**
 * @static @prop {array} required - Required scripts needed for mint to work
 */
App.required = [
	"jquery-2.1.3.min.js",
	"jquery.addons.js",
	"jquery.cookie.js",
	"jquery.mask.min.js",
	"bootstrap.min.js",
	"bootstrap-colorpicker.min.js",
	"bootstrap-datepicker.min.js",
	"knockout-3.3.0.js",
	"extends.js",
	"date.js",
	"tabs.js",
	"form.js",
	"list.js",
	"lang.js",
	"//cdn.ckeditor.com/4.4.7/full/ckeditor.js"
];



/**
 * Initialize the application 
 */
App.prototype.init = function(){
	this.require(App.required, function(){			

		dispatchEvent(new Event("mint-ready"));
		
		this.tabset = new Tabset();
		var self = this;
		$("body").on('click', '[href]:not(.real-link):not([href^="#"])', function(e){
			var url = $(this).attr('href');
			if (url.match(/^javascript\:/)) {
				return true;
			}
			
			e.preventDefault();		
			var data = {};
			
			switch($(this).attr('target')) {
				case 'newtab' :
					// Load the page in a new tab of the application
					data = {newtab : true};
					self.load(url, data);
					break;
				
				case 'dialog' :
					self.dialog(url);
					break;
				
				case '_blank' :
					// Load the whole page in a new browser tab
					return true;
				
				case undefined :
				case '' :
					// Open the url in the current application tab
					self.load(url);
					break;
				
				default :
					// Open the url in a given DOM node, represented by it CSS selector
					self.load(url, {selector : $(this).attr('target')});
					break;
			}	
			
			// return false;
		})
		
		.on('click', ".main-tabs-close", function(){
			self.tabset.remove($(this).data('tab'));
		})

		.on("change", ":file", function(event){
			if(event.currentTarget.files.length){
				$(this).next(".input-file-invitation").removeClass("btn-default").addClass("file-chosen btn-success");				
			}
			else{
				$(this).next(".input-file-invitation").removeClass("file-chosen btn-success").addClass("btn-default");
			}
		});
		
		window.onpopstate = function(event){
			event.preventDefault();
			if(self.tabset.getActiveTab()){
				var history = self.tabset.getActiveTab().history;
				if(history.length > 1){
					history.pop();
					var url = history[history.length - 1];
					self.load(url);
				}
				else{
					self.load(self.getUri("new-tab"));
				}				
			}	
		};

		this.loading = {
			start : function(){
				$('#loading').show();
			},

			progress : function(purcentage){
				$("#loading-purcentage").css("width", purcentage+"%");
				if(purcentage){
					$("#loading-bar").addClass("progressing");
				}
				else{
					$("#loading-bar").removeClass("progressing");	
				}
			},	
			
			stop : function(){
				$('#loading').hide();
				this.progress(0);
			}
		};
	}.bind(this));

	this.xhr = function(){
		var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
                var percentComplete = parseInt(evt.loaded / evt.total * 100);
                //Do something with upload progress here
                window.mint.loading.progress(percentComplete);
            }
        });	 

        xhr.addEventListener("progress", function(evt) {
	        if (evt.lengthComputable) {
	            var percentComplete = parseInt(evt.loaded / evt.total * 100);
                //Do something with upload progress here
                window.mint.loading.progress(percentComplete);
        	}
       	}, false);       

        return xhr;
	}.bind(this);
};



/**
 * Add a callback when the application is ready to run
 */
App.prototype.ready = function(callback){
	addEventListener("mint-ready", callback);
};

App.prototype.load = function(url, data){
	/*** Default options ***/
	var options = {			
		newtab : false,
		callback : null,
		post : null
	};
	
	for(var i in data)
		options[i] = data[i];
		
	if(url){					            
		/*** WE FIRST CHECK THAT PAGE DOES NOT ALREADY EXIST IN A TAB ***/
		if(url != this.getUri('MainController.newTab')){				
			for(var i in this.tabset.tabs){
				if (this.tabset.tabs[i].url == url) {						
					if (i !== this.tabset.getActiveTab().id) {
						this.tabset.activateTab(i);
					}
					// return false;
				}
			}
		}
		
		this.loading.start();

        /*** A new tab has been asked ***/
        if(options.newtab){
            this.tabset.push();
        }
		if(!options.selector)
			options.selector = this.tabset.getActiveTab().getPaneNode();
        
		/*** DETERMINE THE NODE THAT WILL BE LOADED THE PAGE ***/			
		if($(options.selector).length){
			$.ajax({
				xhr : this.xhr,
				url : url, 
				type : options.post ? 'post' : 'get',
				data : options.post
			})
			.done(function(response){					
				this.loading.stop();
				
				$(options.selector).html(response);
				if($(options.selector).get(0) == this.tabset.getActiveTab().getPaneNode().get(0)){
					// The page has been loaded in a whole tab
					// Register the tab url
					var activeTab = this.tabset.getActiveTab();
					activeTab.setUrl(url);
					
					// Set the tab title
					activeTab.setTitle($(options.selector).find(".page-name").first().val());
					
					// Regiter the tabs in the cookie
					this.tabset.registerTabs();
					
					// register the url in the tab history
					this.tabset.getActiveTab().history.push(url);
					
					history.pushState({}, '', "#!" + url);
				}

				if(options.onload){
			        /*** A 'onload' callback has been asked ****/
					options.onload();
				}
			}.bind(this))

			.fail(function(xhr, status, error){
				var code = xhr.status;
				var message = xhr.responseText;
				this.advert("danger", message);
				this.loading.stop();				
			}.bind(this));
		}
		else{
	        /*** The selector to home the loaded url doesn't exist ***/
			this.loading.stop();
			this.advert("danger", Lang.get('main.loading-page-error'));
		}			
	}
	else{
		return false;
	}
};

App.prototype.advert = function(state, message){
	var classname = "alert-"+state;
			
	$("#advert-message").remove();
	$('body').prepend(	"<div id='advert-message' class='alert "+classname+"' onclick='$(this).hide(\"slow\", function(){ $(this).remove() });'>"+								
							"<span>"+message+"</span>"+
							"<span class='close' onclick='$(this).parent().hide(\"slow\", function(){ $(this).remove() });'>&times</span>"+
						"</div>");		
	$("#advert-message").show("slow");
	if(state != "danger"){
		setTimeout(function(){
			$("#advert-message .close").trigger("click");
		}, 3500);
	}

};

App.prototype.dialog = function(action){
	$("#dialogbox").empty();
	if(action == "close"){			
		$("#dialogbox").modal("hide");
		return;
	}
	
	// Load the content from an url
	$.ajax({
		url : action,
		type : 'get',
		data : {
			_dialog: true
		},
		success : function(content){
			$("#dialogbox").append(content).modal("show");								
		}
	});			
};

App.prototype.getUri = function(method, args){		
	var route = null;
	if(method in this.routes){
		route = this.routes[method];
	}
	else{
		for(var i in this.routes){
			if(this.routes[i].action === method){
				route = this.routes[i];
				break;
			}
		}
	}
	
	if(route !== null){
		var url = route.originalUrl;
		if(args){
			for(var j in args){
				url = url.replace("{" + j + "}", args[j]);
			}
		}
		return url;
	}
	else{
		return '';
	}
};

App.prototype.setRoutes = function(routes){
	this.routes = routes;
};

App.prototype.setLanguage = function(language){
	this.language = language;
};

App.prototype.setRootUrl = function(url){
	this.rootUrl = url;
};

var mint = new App();    



/******************* (C) COPYRIGHT 2014 ELVYRRA SAS ********************/