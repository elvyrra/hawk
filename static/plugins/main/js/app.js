'use strict';

/**
 * Start by configure requirejs paths and shim
 */
define(
    'app',
    [
        'jquery',
        'emv',
        'tab',
        'tabs',
        'form',
        'list',
        'lang',
        'cookie',
        'mask',
        'sortable',
        'bootstrap',
        'colorpicker',
        'datepicker',
        'emv-directives'
    ],
    function($, EMV, Tab, Tabset, Form, List, Lang) {
        // export libraries to global context
        window.$ = $;
        window.EMV = EMV;
        window.Tabset = Tabset;
        window.Form = Form;
        window.List = List;
        window.Lang = Lang;

        /**
         * This class describes the behavior of the application
         */
        class App extends EMV {
            /**
             * Constructor
             */
            constructor() {
                super({
                    data : {
                        notification : {
                            display : false,
                            level : undefined,
                            message : ''
                        },

                        menu : {
                            items : window.appConf.menu
                        },
                        tabset : new Tabset(),
                        loading : {
                            display : false,
                            progressing : false,
                            purcentage : 0,


                            /**
                             * Display loading
                             */
                            start : function() {
                                this.display = true;
                            },

                            /**
                             * Show loading progression
                             *
                             * @param {Float} purcentage The advancement purcentage on the progress bar
                             */
                            progress : function(purcentage) {
                                this.purcentage = purcentage;
                                this.progressing = Boolean(purcentage);
                            },

                            /**
                             * Hide loading
                             */
                            stop : function() {
                                this.display = false;
                                this.progress(0);
                            }
                        },
                        dialogbox : new EMV({
                            data : {
                                display : false,
                                content : '',
                                width : '',
                                height : ''
                            },
                            computed : {
                                title : function() {
                                    return $('.page-name', this.content).first().val() || '';
                                },
                                width : function() {
                                    return $('.page-width', this.content).first().val() || '';
                                },
                                height : function() {
                                    return $('.page-height', this.content).first().val() || '';
                                },
                                icon : function() {
                                    const value = $('.page-icon', this.content).first().val() || null;

                                    try {
                                        const url = new URL(value);

                                        return url && null;
                                    }
                                    catch(err) {
                                        return value;
                                    }
                                },
                                favicon : function() {
                                    const value = $('.page-icon', this.content).first().val() || null;

                                    try {
                                        const url = new URL(value);

                                        return url ? value : null;
                                    }
                                    catch(err) {
                                        return null;
                                    }
                                }
                            }
                        })
                    }
                });
                this.conf = window.appConf;
                this.language = '';
                this.rootUrl = '';
                this.isLogged = false;
                this.routes = [];
                this.forms = {};
                this.lists = {};
            }

            /**
             * Initialize the application
             */
            start() {
                // Set the configuration data
                this.setLanguage(this.conf.Lang.language);
                this.setRoutes(this.conf.routes);
                this.setRootUrl(this.conf.rooturl);
                Lang.init(this.conf.Lang.keys);
                this.baseUrl = require.toUrl('');
                this.isLogged = this.conf.user.logged;

                /**
                 * Call URIs by AJAX on click on links
                 */
                var linkSelector =  '[href]:not([href^="#"]):not([href^="javascript:"]),' +
                                    '[data-href]:not([data-href^="#"]):not([data-href^="javascript:"])';

                $('body').on('click', linkSelector, (event) => {
                    var node = $(event.currentTarget);
                    var url  = $(node).attr('href') || $(node).data('href');

                    if(url.match(/^(#|javascript\:|mailto\:)/)) {
                        return true;
                    }

                    event.preventDefault();
                    var data = {},
                        target = $(node).attr('target') || $(node).data('target');

                    if ((event.which === 2 || !this.tabset.tabs.length) && !target) {
                        target = 'newtab';
                    }

                    switch (target) {
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
                            location.href = url;
                            break;

                        default :
                            // Open the url in a given DOM node, represented by it CSS selector
                            this.load(url, {selector : target});
                            break;
                    }
                })

                // Open a link in a new tab of the application
                .on('mousedown', linkSelector, function(event) {
                    if (event.which === 2) {
                        if (!$(this).attr('target')) {
                            event.type = 'click';

                            var clickEvent = new Event('click', event);

                            clickEvent.which = 2;
                            $(this).get(0).dispatchEvent(clickEvent);
                        }

                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();

                        return false;
                    }

                    return true;
                });

                /**
                 * Treat back button
                 *
                 * @param {Event} event The popstate event
                 */
                window.onpopstate = (event) => {
                    event.preventDefault();
                    if (this.tabset.activeTab) {
                        var history = this.tabset.activeTab.history;

                        if (event.state) {
                            // call back button
                            if (history.length > 1) {
                                history.pop();
                                this.load(history[history.length - 1]);
                            }
                            else {
                                this.load(this.getUri('new-tab'));
                            }
                        }
                        else if (location.hash.match(/^#\!(\/.*?)$/)) {
                            // Load a new page in the current tab
                            var hash = location.hash.replace(/^#\!(\/.*?)$/, '$1');

                            this.load(hash);
                        }
                        else {
                            // Click on a link with an anchor as href
                            window.history.replaceState({}, '', '#!' + history[history.length - 1]);
                        }
                    }
                };

                /**
                 * Customize app HttpRequestObject
                 *
                 * @returns {XMLHttpRequest} The xhr object
                 */
                this.xhr = function() {
                    const xhr = new window.XMLHttpRequest();

                    this.computeProgession = function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = parseInt(evt.loaded / evt.total * 100);

                            // Do something with upload progress here
                            this.loading.progress(percentComplete);
                        }
                    }.bind(this);

                    /**
                     * Compute progression on upload AJAX requests
                     */
                    xhr.upload.addEventListener('progress', this.computeProgession);

                    /**
                     * Compute progression on AJAX requests
                     */
                    xhr.addEventListener('progress', this.computeProgession);

                    return xhr;
                }.bind(this);

                /**
                 * Open the last tabs
                 */
                const route = this.getRouteFromUri(location.pathname);
                const finalRoutes = [
                    'install',
                    'install-settings',
                    'validate-third-registration'
                ];

                if(finalRoutes.indexOf(route) !== -1) {
                    // Do not load any new page in installation pages
                    return;
                }

                const hash = location.hash.replace(/^#\!/, '');

                if (hash && hash !== 'undefined') {
                    var index = this.conf.tabs.open.indexOf(hash);

                    if (index === -1) {
                        if (this.conf.tabs.open.length === 1) {
                            this.conf.tabs.open = [hash];
                        }
                        else {
                            this.conf.tabs.open.push(hash);
                        }
                    }

                    index = this.conf.tabs.open.indexOf(hash);

                    this.tabset.activeTab = this.tabset.tabs[index];
                }

                this.openLastTabs(this.conf.tabs.open);
            }

            /**
             * Load a page in the current tab, or a new tab, or a given html node
             *
             * @param {string} uri  The url to load
             * @param {Object} data The options. This object can hasve the following data :
             *                           - newtab (default false) : if set to true, the page will be loaded in a new tab of the application
             *                           - onload (default null) : A callback function to execute when the page is loaded
             *                           - post (default null) : an object of POST data to send in the URL
             *
             * @returns {Promise} A promise resolved if the page is succesfully loaded
             */
            load(uri, data) {
                /**
                 * Default options
                 */
                const options = {
                    newtab : false,
                    onload : null,
                    post : null,
                    selector : null,
                    headers : {}
                };
                let url = uri;

                if(data) {
                    Object.keys(data).forEach(function(key) {
                        options[key] = data[key];
                    });
                }

                if(url) {
                    /**
                     * We first check that page does not already exist in a tab
                     */
                    const route = this.getRouteFromUri(url);

                    if (route === 'new-tab') {
                        url = this.conf.tabs.new.url;
                    }

                    for (let j = 0; j < this.tabset.tabs.length; j++) {
                        const tab = this.tabset.tabs[j];

                        if (tab.uri === url || tab.route === route && !this.routes[route].duplicable) {
                            this.tabset.activeTab = tab;
                            options.newtab = false;
                            break;
                        }
                    }

                    this.loading.start();

                    /**
                     * A new tab has been asked
                     */
                    if (options.newtab) {
                        this.tabset.push();
                    }

                    // Get the element the page will be loaded in
                    const element = options.selector ? $(options.selector).get(0) : this.tabset.activeTab;

                    if (!element) {
                        /**
                         * The selector to home the loaded url doesn't exist
                         */
                        this.loading.stop();
                        this.notify('danger', Lang.get('main.loading-page-selector-not-exists'));

                        return new $.Deferred();
                    }

                    // Load the page
                    let query = '';

                    if (options.query) {
                        var params = [];

                        Object.keys(options.query).forEach(function(param) {
                            params.push(param + '=' + encodeURIComponent(options.query[param]));
                        });

                        query = '?' + params.join('&');

                        url = url + query;
                    }

                    let beforeChange = Promise.resolve();

                    if(element instanceof Tab) {
                        beforeChange = element.trigger('before.change', url);
                    }

                    return beforeChange

                    .then(() => {
                        return $.ajax({
                            xhr : this.xhr,
                            url : url,
                            type : options.post ? 'post' : 'get',
                            data : options.post,
                            dataType : 'text',
                            headers : options.headers
                        })

                        .then((response) => {
                            this.loading.stop();

                            if (element instanceof Tab) {
                                // The page has been loaded in a whole tab
                                // Register the tab url
                                element.uri = url;
                                element.route = route;

                                element.content = response;

                                // Regiter the tabs in the cookie
                                if (this.isLogged) {
                                    this.tabset.registerTabs();
                                }

                                // register the url in the tab history
                                element.history.push(url);

                                history.pushState({}, '', '#!' + url);

                                return element.trigger('after.change');
                            }

                            $(element).html(response);

                            return true;
                        })

                        .catch((xhr) => {
                            this.loading.stop();
                            var code = xhr.status;

                            // The page is not accessible for the user
                            var response;

                            try {
                                response = JSON.parse(xhr.responseText);
                            }
                            catch (e) {
                                response = {
                                    message : xhr.responseText
                                };
                            }

                            if (code === 401) {
                                // The user is not connected, display the login form
                                this.dialog(this.getUri('login-form', {}, {
                                    redirect : url,
                                    code : code
                                }));

                                return Promise.reject(new Error(response.message));
                            }

                            this.notify('danger', response.message);

                            return Promise.reject(new Error(response.message));
                        });
                    })

                    .catch(() => {
                        this.loading.stop();
                    });
                }

                return Promise.reject();
            }

            /**
             * Open a set of pages
             *
             * @param {Array} uris The uris to open, each one in a tab
             * @param {Function} onload The callback to execute when all the tabs are loaded
             * @returns {Promise} A promise reslved when all te tabs are open
             */
            openLastTabs(uris) {
                // var loaded = 0;

                return $.when.apply(this, uris.map((uri) => {
                    return this.load(uri, {
                        newtab : true
                    });
                }))

                .done(() => {
                    this.loading.stop();

                    return uris;
                });
            }

            /**
             * Display a notification on the application or on the user desktop
             *
             * @param {string} level   The notification level (info, success, warning, danger or desktop)
             * @param {string} message The message to display in the notification
             * @param {Object} options The options for desktop notifications
             */
            notify(level, message, options) {
                if (level === 'error') {
                    level = 'danger';
                }

                if (level === 'desktop') {
                    // this is a desktop notification
                    if (!('Notification' in window)) {
                        this.notify('success', message);
                    }
                    else if (Notification.permission === 'granted') {
                        const notif = new Notification(message, options);
                    }
                    else if (Notification.permission !== 'denied') {
                        // Ask for user permission to display notifications
                        Notification.requestPermission(function(permission) {
                            Notification.permission = permission;
                            this.notify(level, message, options);
                        }.bind(this));
                    }
                }
                else {
                    // Display an advert message in the application
                    this.notification.display = true;
                    this.notification.message = message;
                    this.notification.level = level;

                    if (level !== 'danger') {
                        this.notification.timeout = setTimeout(function() {
                            this.hideNotification();
                        }.bind(this), 5000);
                    }
                }
            }

            /**
             * Hide the displayed notification
             */
            hideNotification() {
                clearTimeout(this.notification.timeout);

                this.notification.display = false;
            }

            /**
             * Load a URL in a dialog box
             *
             * @param {string} action  The action to perform. If "close", it will wlose the current dialog box,
             *                           else it will load the action in the dialog box and open it
             * @param {Object} options The options object :
             *                         - onload : A callback function, executed after the dialogbox has been displayed
             * @returns {Object} The jquery Ajax 'promise'
             */
            dialog(action, options) {
                options = options || {};

                this.dialogbox.display = false;
                this.dialogbox.content = '';

                if (action === 'close') {
                    return null;
                }

                // Load the content from an url
                this.loading.start();

                return $.ajax({
                    url : action,
                    type : 'get',
                    data : {
                        _dialog: true
                    }
                })

                .done((content) => {
                    this.loading.stop();

                    // Page successfully loaded
                    this.dialogbox.display = true;
                    this.dialogbox.content = content;

                    if (options.onload) {
                        options.onload();
                    }

                    return content;
                })

                .fail((xhr) => {
                    // Page load failed
                    var response;
                    this.loading.stop();

                    try {
                        response = JSON.parse(xhr.responseText);
                    }
                    catch(err) {
                        response = {
                            message : xhr.responseText
                        };
                    }

                    this.notify('danger', response.message);
                });
            }

            /**
             * Get uri for a given route name or the controller of the route
             *
             * @param {string} method      The route name or the controller method executed by this route
             * @param {Object} args        The route parameters
             * @param {Object} queryString Query string parameters to add
             *
             * @returns {string}           The computed URI
             */
            getUri(method, args, queryString) {
                var route = null;

                if (method in this.routes) {
                    route = this.routes[method];
                }
                else {
                    for (var i in this.routes) {
                        if (this.routes[i].action === method) {
                            route = this.routes[i];
                            break;
                        }
                    }
                }

                if(!route) {
                    return App.INVALID_URI;
                }

                var url = route.url;

                if (args) {
                    for (var j in args) {
                        if (args.hasOwnProperty(j)) {
                            url = url.replace('{' + j + '}', args[j]);
                        }
                    }
                }

                if(queryString) {
                    url += '?' + $.param(queryString);
                }

                return this.conf.basePath + url;
            }

            /**
             * Get the route name corresponding to an URI
             *
             * @param    {string} uri - The uri to look the corresponding route for
             * @returns   {Object} The found route
             */
            getRouteFromUri(uri) {
                var path = uri.replace(/\/?\?.*$/, '');

                for (var i in this.routes) {
                    if (this.routes.hasOwnProperty(i)) {
                        var regex = new RegExp('^' + this.routes[i].pattern + '$');

                        if (path.match(regex)) {
                            return i;
                        }
                    }
                }

                return null;
            }

            /**
             * Get the route corresponding to an URI. This method turns the route name and the path parameters
             * @param   {string} uri The uri to look the corresponding route for
             * @returns {Object}     The found route parameters, or null
             */
            getRouteInformationFromUri(uri) {
                var path = uri.replace(/\/?\?.*$/, '');

                for (var i in this.routes) {
                    if (this.routes.hasOwnProperty(i)) {
                        const regex = new RegExp('^' + this.routes[i].pattern + '$');
                        const match = path.match(regex);

                        if (path.match(regex)) {
                            const result = {
                                name : i,
                                data : {}
                            };

                            Object.keys(this.routes[i].where).forEach((key, index) => {
                                result.data[key] = match[index + 1];
                            });

                            return result;
                        }
                    }
                }

                return null;
            }

            /**
             * Set the existing routes of the application
             *
             * @param {Object} routes The routes to set
             */
            setRoutes(routes) {
                this.routes = routes;
            }

            /**
             * Set the language of the application
             *
             * @param {string} language The language tag
             */
            setLanguage(language) {
                this.language = language;
            }

            /**
             * Set the root url of the application
             *
             * @param {string} url The root url to set
             */
            setRootUrl(url) {
                this.rootUrl = url;
            }

            /**
             * Refresh the main menu
             */
            refreshMenu() {
                $.get(this.getUri('refresh-menu'), (response) => {
                    this.menu.items = response;

                    this.notify('warning', Lang.get('main.main-menu-changed'));
                });
            }

            /**
             * Print a part of the page (or the whole page)
             *
             * @param {NodeElement} element The DOM element to print.
             *                              If not set or null, then this will print the whole page
             * @memberOf App
             */
            print(element) {
                if (!element) {
                    window.print();
                }
                else {
                    // Create a frame to wrap the content to print
                    var frame = document.createElement('iframe');

                    document.body.appendChild(frame);

                    // Add the content to the page
                    frame.contentDocument.body.innerHTML = element.outerHTML;
                    frame.style.display = 'none';

                    // Add the css
                    var style    = document.createElement('link');

                    style.rel = 'stylesheet';
                    style.href = document.getElementById('theme-base-stylesheet').href;
                    style.type = 'text/css';
                    style.media = 'print';
                    style.onload = function() {
                        frame.contentWindow.print();
                        frame.contentWindow.close();
                        frame.remove();
                    };
                    frame.contentDocument.head.appendChild(style);
                }
            }

            /**
             * Reload all the routes from the server
             * @returns {Promise} Resolved if the routes has been successfully reloaded
             */
            reloadRoutes() {
                return $.getJSON(this.getUri('all-routes'))

                .done((routes) => {
                    this.setRoutes(routes);
                });
            }
        }

        /**
         * The URI to return for non existing route
         *
         * @constant
         * @memberOf App
         */
        App.INVALID_URI = window.appConf.basePath + '/INVALID_URI';



        // Instanciate the application
        const app = new App();

        if (!window.app) {
            window.app = app;
        }

        app.start();

        return app;
    }
);