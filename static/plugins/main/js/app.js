"use strict";

/**
 *  Start by configure requirejs paths and shim
 */
require.config({
	paths : {
		jquery : "ext/jquery-2.1.3.min",
		cookie : "ext/jquery.cookie",
		mask : "ext/jquery.mask.min",
		sortable : "ext/jquery-sortable",
		bootstrap : "ext/bootstrap.min",
		colorpicker : "ext/bootstrap-colorpicker.min",
		datepicker : "ext/bootstrap-datepicker.min",
		ko : "ext/knockout-3.3.0",
		ckeditor : "ext/ckeditor/ckeditor",
		ace : "ext/ace/ace"
	},

	shim : {
		jquery : {
			exports : '$'
		},
		ko : {
			exports : 'ko'
		},

		cookie : {
			deps : ['jquery'],
		},
		mask : {
			deps : ['jquery']
		},
		sortable : {
			deps : ['jquery']
		},
		bootstrap : {
			deps : ['jquery']
		},
		datepicker : {
			deps : ['bootstrap']
		},
		colorpicker: {
			deps : ['bootstrap']
		},
		'ko-extends' : {
			deps : ['ko']
		},
		ace : {
			exports : 'ace'
		},
		ckeditor : {
			exports : 'CKEDITOR'
		}
	}
});

/**
 * @class App - This class describes the behavior of the application
 */
var App = function(){
	this.language = ''; // The application language
	this.rootUrl = ''; // The application root url
	this.isConnected = false; // The user is connected or not ?
	this.routes = []; // The application routes
	this.forms = {}; // The instanciated forms
	this.lists = {}; // The instanciated lists

	this.isReady = false; // The ready state of the application
};


/**
 * @const {string} INVALID_URI - The URI to return for non existing route
 */
App.INVALID_URI = appConf.basePath + '/INVALID_URI';

/**
 * Initialize the application
 */
App.prototype.start = function(){
	define('app', ['jquery' ,'ko', 'tabs', 'form', 'list', 'lang', 'cookie','mask', 'sortable', 'bootstrap', 'colorpicker' , 'datepicker', 'ko-extends', 'extends' , 'date' ], function($, ko, Tabset, Form, List, Lang) {
		// export libraries to global context
		window.$ = $;
		window.ko = ko;
		window.Tabset = Tabset;
		window.Form = Form;
		window.List = List;
		window.Lang = Lang;

		// Set the configuration data
		this.setLanguage(appConf.Lang.language);
		this.setRoutes(appConf.routes);
		this.setRootUrl(appConf.rooturl);
		Lang.init(appConf.Lang.keys);
		this.baseUrl = require.toUrl('');
		this.isConnected = appConf.user.connected;


		// Manage the notification area
		this.notification = {
			display : ko.observable(false),
			level : ko.observable(),
			message : ko.observable()
		}

		this.tabset = new Tabset();

		/**
		 * Call URIs by AJAX on click on links
		 */
		var linkSelector = '[href]:not(.real-link):not([href^="#"]):not([href^="javascript:"])';
		$("body").on('click', linkSelector, function(event){
			var node = $(event.currentTarget);
			var url = $(node).attr('href');

			event.preventDefault();
			var data = {};

			var target = $(node).attr('target');
			if((event.which == 2 || !this.tabset.tabs().length) && ! target) {
				target = "newtab";
			}
			switch(target) {
				case 'newtab' :
					// Load the page in a new tab of the application
					data = {newtab : true};
					this.load(url, data);
					break;

				case 'dialog' :
					this.dialog(url);
					break;

				case '_blank' :
					// Load the whole page in a new browser tab
					window.open(url);
					break;

				case undefined :
				case '' :
					// Open the url in the current application tab
					this.load(url);
					break;

				case 'window' :
					// Open the URL in the current web page
					location = url;
					break

				default :
					// Open the url in a given DOM node, represented by it CSS selector
					this.load(url, {selector : $(node).attr('target')});
					break;
			}
		}.bind(this))

		// Open a link in a new tab of the application
		.on("mousedown", linkSelector, function(event){
			if(event.which == 2){
				if(! $(this).attr('target')){
					event.type = "click";

					var clickEvent = new Event("click", event);
					clickEvent.which =2;
					$(this).get(0).dispatchEvent(clickEvent);
				}

				event.preventDefault();
				event.stopPropagation();
				event.stopImmediatePropagation();
				return false;
			}
		});

		/**
		 * Treat back button
		 */
		window.onpopstate = function(event){
			event.preventDefault();
			if(this.tabset.activeTab()){
				var history = this.tabset.activeTab().history;
				if(event.state){
					// call back button
					if(history.length > 1){
						history.pop();
						var url = history[history.length - 1];
						this.load(url);
					}
					else{
						this.load(this.getUri("new-tab"));
					}
				}
				else{
					// Click on a link with an anchor as href
					var url = history[history.length - 1];
					window.history.replaceState({}, '', "#!" + url);
				}
			}
		}.bind(this);

		this.loading = {
			display : ko.observable(false),
			progressing : ko.observable(false),
			purcentage : ko.observable(0),

			/**
			 * Display loading
			 */
			start : function(){
				this.display(true);
			},

			/**
			 * show loading progression
			 */
			progress : function(purcentage){
				this.purcentage(purcentage);
				this.progressing(purcentage ? true : false);
			},

			/**
			 * Hide loading
			 */
			stop : function(){
				this.display(false);
				this.progress(0);
			}
		};


		// trigger the application is ready
		var evt = document.createEvent("Event");
		evt.initEvent("app-ready", true, false);
		dispatchEvent(evt);

		/**
		 * Customize app HttpRequestObject
		 */
		this.xhr = function(){
			var xhr = new window.XMLHttpRequest();

	        this.computeProgession = function(evt){
	        	if (evt.lengthComputable) {
	                var percentComplete = parseInt(evt.loaded / evt.total * 100);
	                //Do something with upload progress here
	                this.loading.progress(percentComplete);
	            }
	        }.bind(this);

	        /**
	         * Compute progression on upload AJAX requests
	         */
	        xhr.upload.addEventListener("progress", this.computeProgession);

	        /**
	         * Compute progression on AJAX requests
	         */
	        xhr.addEventListener("progress", this.computeProgession);

	        return xhr;

		}.bind(this);



		/**
		 * Open the tab asked by the user when it was not connected
		 */
		if(this.isConnected && $.cookie('redirect')){
			appConf.tabs.open.push($.cookie('redirect'));
			$.cookie('redirect', '', {expires : new Date()});
		}

		/**
		 * Open the last tabs
		 */
		this.openLastTabs(appConf.tabs.open);



	}.bind(this));
};



/**
 * Add a callback when the application is ready to run
 */
App.prototype.ready = function(callback){
	if (this.isReady) {
		callback();
	}
	else{
		addEventListener("app-ready", function(){
			this.isReady = true;
			callback();
		}.bind(this));
	}
};


/**
 * Load a page in the current step, or a new step, or a given html node
 * @param {string} url The url to load
 * @param {object} data, the options. This object can hasve the following data :
  	- newtab (default false) : if set to true, the page will be loaded in a new tab of the application
  	- onload (default null) : A callback function to execute when the page is loaded
  	- post (default null) : an object of POST data to send in the URL
 */
App.prototype.load = function(url, data){
	/*** Default options ***/
	var options = {
		newtab : false,
		onload : null,
		post : null,
		selector : null
	};

	for(var i in data){
		options[i] = data[i];
	}

	if(url){
		/*** we first check that page does not already exist in a tab ***/
		var route = this.getRouteFromUri(url);

		for(var i= 0; i < this.tabset.tabs().length; i++){
			var tab = this.tabset.tabs()[i];
			if (tab.url() == url || tab.route() == route) {
				if (tab !== this.tabset.activeTab()) {
					this.tabset.activeTab(tab);
				}
				options.newtab = false;
				break;
			}
		}

		this.loading.start();

        /*** A new tab has been asked ***/
        if(options.newtab){
            this.tabset.push();
        }

		var element = options.selector ? $(options.selector).get(0) : this.tabset.activeTab();

		/*** DETERMINE THE NODE THAT WILL BE LOADED THE PAGE ***/
		if(element){
			$.ajax({
				xhr : this.xhr,
				url : url,
				type : options.post ? 'post' : 'get',
				data : options.post,
				dataType : 'text',
			})
			.done(function(response){
				this.loading.stop();

				if(element === this.tabset.activeTab()){
					// The page has been loaded in a whole tab
					// Register the tab url
					element.url(url);
					element.route(route);

					// Set the tab title
					element.title($(".page-name", response).first().val());

					element.content(response);

					// Regiter the tabs in the cookie
					if(this.isConnected) {
						this.tabset.registerTabs();
					}

					// register the url in the tab history
					element.history.push(url);

					history.pushState({}, '', "#!" + url);
				}
				else{
					$(element).html(response);
				}

				if(options.onload){
			        /*** A 'onload' callback has been asked ****/
					options.onload();
				}
			}.bind(this))

			.fail(function(xhr, status, error){
				var code = xhr.status;

				if(code === 403){
					// The page is not accessible for the user
					var response;
					try{
						response = JSON.parse(xhr.responseText);
					}
					catch(e){
						response = {
							message : Lang.get('main.access-forbidden')
						};
					}

					if(response.reason == "login"){
						// The user is not connected, display the login form
						this.dialog(this.getUri('login') + '?redirect=' + url + '&code=' + code);
					}
					else{
						// Other reason, display the message in a notification
						var message = response.message;
						this.notify("danger", message);
					}
				}
				else{
					var message = xhr.responseText;
					this.notify("danger", message);
				}

				this.loading.stop();
			}.bind(this));
		}
		else{
	        /*** The selector to home the loaded url doesn't exist ***/
			this.loading.stop();
			this.notify("danger", Lang.get('main.loading-page-selector-not-exists'));
		}
	}
	else{
		return false;
	}
};


/**
 * Open a set of pages
 */
App.prototype.openLastTabs = function(uris){
	if(!uris.length){
		// No more tab has to be open
		return;
	}

	var uri = uris.shift();
	this.load(uri, {
		newtab : true,
		onload : this.openLastTabs.bind(this, uris)
	});
};


/**
 * Display a notification on the application or on the user desktop
 * @param {string} level - The notification level (info, success, warning, danger or desktop)
 * @param {string} message - The message to display in the notification
 * @parma {object} options - The options for desktop notifications
 */
App.prototype.notify = function(level, message, options){
	if(level === "error"){
		level = "danger";
	}
	if(level == "desktop"){
		// this is a desktop notification
		if(! ('Notification' in window)){
			this.notify('success', message);
		}
		else if(Notification.permission === 'granted'){
			var notification = new Notification(message, options);
		}
		else if(Notification.permission !== 'denied'){
			// Ask for user permission to display notifications
			Notification.requestPermission(function(permission){
				Notification.permission = permission;

				this.notify(level, message, options);
			}.bind(this));
		}
	}
	else{
		// Display an advert message in the application
		this.notification.display(true);
		this.notification.message(message);
		this.notification.level(level);

		if(level != "danger"){
			this.notification.timeout = setTimeout(function(){
				this.hideNotification();
			}.bind(this), 5000);
		}
	}
};

App.prototype.hideNotification = function(){
	clearTimeout(this.notification.timeout);
	this.notification.display(false);
}


/**
 * Load a URL in a dialog box
 * @param {string} action - The action to perform. If "close", it will wlose the current dialog box, else it will load the action in the dialog box and open it
 */
App.prototype.dialog = function(action){
	$("#dialogbox").empty().modal('hide');

	if(action == "close"){
		return;
	}

	// Load the content from an url
	$.ajax({
		url : action,
		type : 'get',
		data : {
			_dialog: true
		},
	})
	.done(function(content){
		$("#dialogbox").append(content).modal("show");
	})
	.fail(function(xhr, status, error){
		var code = xhr.status;
		var message = xhr.responseText;
		this.notify("danger", message);
	}.bind(this));
};


/**
 * Get uri for a given route name or the controller of the route
 * @param {string} method - The route name or the controller method executed by this route
 * @param {object} args - The route parameters
 * @return {string} - the computed URI
 */
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
		var url = route.url;
		if(args){
			for(var j in args){
				url = url.replace("{" + j + "}", args[j]);
			}
		}
		return appConf.basePath + url;
	}
	else{
		return App.INVALID_URI;
	}
};


/**
 * Get the route name corresponding to an URI
 * @param {string} uri - The uri to look the corresponding route for
 */
App.prototype.getRouteFromUri = function(uri){
	for(var i in this.routes){
		var regex = new RegExp('^' + this.routes[i].pattern + '$');
		if(uri.match(regex)){
			return i;
		}
	}
};


/**
 * Set the existing routes of the application
 * @param {object} routes - The routes to set
 */
App.prototype.setRoutes = function(routes){
	this.routes = routes;
};


/**
 * Set the language of the application
 * @param {string} language - The language tag
 */
App.prototype.setLanguage = function(language){
	this.language = language;
};


/**
 * Set the root url of the application
 * @param {string} url - The root url to set
 */
App.prototype.setRootUrl = function(url){
	this.rootUrl = url;
};


App.prototype.refreshMenu = function(){
    $.get(this.getUri('refresh-menu'), function(response){
        $("#main-menu").replaceWith(response);
		this.notify('warning', Lang.get('admin.plugins-advert-menu-changed'));
    }.bind(this));
};

window.app = new App();

app.ready(function(){
	ko.applyBindings(app);
});

app.start();
