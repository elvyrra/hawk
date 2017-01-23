'use strict';

/**
 * Start by configure requirejs paths and shim
 */
// var baseUrl = './';

// if(typeof document !== 'undefined') {
//     baseUrl = document.getElementById('app-main-script').src.replace(/^(.+\/).+$/, '$1');
// }

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

require.config({
    // Workaround to be optimized by r.js
    // baseUrl :  typeof baseUrl === 'undefined' ? './' : baseUrl,

    paths: {
        jquery: 'ext/jquery-last.min',
        cookie: 'ext/jquery.cookie',
        mask: 'ext/jquery.mask.min',
        sortable: 'ext/jquery-sortable',
        bootstrap: 'ext/bootstrap.min',
        colorpicker: 'ext/bootstrap-colorpicker.min',
        datepicker: 'ext/bootstrap-datepicker.min',
        ckeditor: 'ext/ckeditor/ckeditor',
        ace: 'ext/ace/ace',
        less: 'ext/less',
        moment: 'ext/moment.min',
        // Workaround to build app-es5 with npm
        emv: 'ext/emv.min'
    },
    shim: {
        jquery: {
            exports: '$'
        },
        ko: {
            exports: 'ko'
        },
        cookie: {
            deps: ['jquery']
        },
        mask: {
            deps: ['jquery']
        },
        sortable: {
            deps: ['jquery']
        },
        bootstrap: {
            deps: ['jquery']
        },
        datepicker: {
            deps: ['bootstrap']
        },
        colorpicker: {
            deps: ['bootstrap']
        },
        'emv-directives': {
            deps: ['emv']
        },
        ace: {
            exports: 'ace'
        },
        ckeditor: {
            exports: 'CKEDITOR'
        },
        moment: {
            exports: 'moment'
        },
        shim: {
            emv: {
                exports: 'EMV'
            }
        }
    }
});

define('app', ['jquery', 'emv', 'tab', 'tabs', 'form', 'list', 'lang', 'cookie', 'mask', 'sortable', 'bootstrap', 'colorpicker', 'datepicker', 'emv-directives'], function ($, EMV, Tab, Tabset, Form, List, Lang) {
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

    var App = function (_EMV) {
        _inherits(App, _EMV);

        /**
         * Constructor
         */
        function App() {
            _classCallCheck(this, App);

            var _this = _possibleConstructorReturn(this, (App.__proto__ || Object.getPrototypeOf(App)).call(this, {
                data: {
                    notification: {
                        display: false,
                        level: undefined,
                        message: ''
                    },

                    menu: {
                        items: window.appConf.menu
                    },
                    tabset: new Tabset(),
                    loading: {
                        display: false,
                        progressing: false,
                        purcentage: 0,

                        /**
                         * Display loading
                         */
                        start: function start() {
                            this.display = true;
                        },

                        /**
                         * Show loading progression
                         *
                         * @param {Float} purcentage The advancement purcentage on the progress bar
                         */
                        progress: function progress(purcentage) {
                            this.purcentage = purcentage;
                            this.progressing = Boolean(purcentage);
                        },

                        /**
                         * Hide loading
                         */
                        stop: function stop() {
                            this.display = false;
                            this.progress(0);
                        }
                    },
                    dialogbox: new EMV({
                        data: {
                            display: false,
                            content: '',
                            width: '',
                            height: ''
                        },
                        computed: {
                            title: function title() {
                                return $('.page-name', this.content).first().val() || '';
                            },
                            width: function width() {
                                return $('.page-width', this.content).first().val() || '';
                            },
                            height: function height() {
                                return $('.page-height', this.content).first().val() || '';
                            },
                            icon: function icon() {
                                var value = $('.page-icon', this.content).first().val() || null;

                                try {
                                    var url = new URL(value);

                                    return url && null;
                                } catch (err) {
                                    return value;
                                }
                            },
                            favicon: function favicon() {
                                var value = $('.page-icon', this.content).first().val() || null;

                                try {
                                    var url = new URL(value);

                                    return url ? value : null;
                                } catch (err) {
                                    return null;
                                }
                            }
                        }
                    })
                }
            }));

            _this.conf = window.appConf;
            _this.language = '';
            _this.rootUrl = '';
            _this.isLogged = false;
            _this.routes = [];
            _this.forms = {};
            _this.lists = {};
            return _this;
        }

        /**
         * Initialize the application
         */


        _createClass(App, [{
            key: 'start',
            value: function start() {
                var _this2 = this;

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
                var linkSelector = '[href]:not([href^="#"]):not([href^="javascript:"]),' + '[data-href]:not([data-href^="#"]):not([data-href^="javascript:"])';

                $('body').on('click', linkSelector, function (event) {
                    var node = $(event.currentTarget);
                    var url = $(node).attr('href') || $(node).data('href');

                    event.preventDefault();
                    var data = {},
                        target = $(node).attr('target') || $(node).data('target');

                    if ((event.which === 2 || !_this2.tabset.tabs.length) && !target) {
                        target = 'newtab';
                    }

                    switch (target) {
                        case 'newtab':
                            // Load the page in a new tab of the application
                            data = { newtab: true };
                            _this2.load(url, data);
                            break;

                        case 'dialog':
                            _this2.dialog(url);
                            break;

                        case '_blank':
                            // Load the whole page in a new browser tab
                            window.open(url);
                            break;

                        case undefined:
                        case '':
                            // Open the url in the current application tab
                            _this2.load(url);
                            break;

                        case 'window':
                            // Open the URL in the current web page
                            location.href = url;
                            break;

                        default:
                            // Open the url in a given DOM node, represented by it CSS selector
                            _this2.load(url, { selector: target });
                            break;
                    }
                })

                // Open a link in a new tab of the application
                .on('mousedown', linkSelector, function (event) {
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
                window.onpopstate = function (event) {
                    event.preventDefault();
                    if (_this2.tabset.activeTab) {
                        var history = _this2.tabset.activeTab.history;

                        if (event.state) {
                            // call back button
                            if (history.length > 1) {
                                history.pop();
                                _this2.load(history[history.length - 1]);
                            } else {
                                _this2.load(_this2.getUri('new-tab'));
                            }
                        } else if (location.hash.match(/^#\!(\/.*?)$/)) {
                            // Load a new page in the current tab
                            var hash = location.hash.replace(/^#\!(\/.*?)$/, '$1');

                            _this2.load(hash);
                        } else {
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
                this.xhr = function () {
                    var xhr = new window.XMLHttpRequest();

                    this.computeProgession = function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = parseInt(evt.loaded / evt.total * 100);

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
                var hash = location.hash.replace(/^#\!/, '');

                if (hash) {
                    var index = this.conf.tabs.open.indexOf(hash);

                    if (index === -1) {
                        if (this.conf.tabs.open.length === 1) {
                            this.conf.tabs.open = [hash];
                        } else {
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
             * @param {string} url The url to load
             * @param {Object} data, the options. This object can hasve the following data :
             *     - newtab (default false) : if set to true, the page will be loaded in a new tab of the application
             *    - onload (default null) : A callback function to execute when the page is loaded
             *    - post (default null) : an object of POST data to send in the URL
             *
             * @returns {Promise} A promise resolved if the page is succesfully loaded
             */

        }, {
            key: 'load',
            value: function load(url, data) {
                /**
                 * Default options
                 */
                var options = {
                    newtab: false,
                    onload: null,
                    post: null,
                    selector: null,
                    headers: {}
                };

                if (data) {
                    Object.keys(data).forEach(function (key) {
                        options[key] = data[key];
                    });
                }

                if (url) {
                    /**
                     * We first check that page does not already exist in a tab
                     */
                    var route = this.getRouteFromUri(url);

                    if (route === 'new-tab') {
                        url = this.conf.tabs.new.url;
                    }

                    for (var j = 0; j < this.tabset.tabs.length; j++) {
                        var tab = this.tabset.tabs[j];

                        if (tab.uri === url || tab.route === route && !this.routes[route].duplicable) {
                            if (tab !== this.tabset.activeTab) {
                                this.tabset.activeTab = tab;
                                return new $.Deferred();
                            }

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
                    var element = options.selector ? $(options.selector).get(0) : this.tabset.activeTab;

                    if (!element) {
                        /**
                         * The selector to home the loaded url doesn't exist
                         */
                        this.loading.stop();
                        this.notify('danger', Lang.get('main.loading-page-selector-not-exists'));

                        return new $.Deferred();
                    }

                    // Load the page
                    var query = '';

                    if (options.query) {
                        var params = [];

                        Object.keys(options.query).forEach(function (param) {
                            params.push(param + '=' + encodeURIComponent(options.query[param]));
                        });

                        query = '?' + params.join('&');

                        url = url + query;
                    }

                    return $.ajax({
                        xhr: this.xhr,
                        url: url,
                        type: options.post ? 'post' : 'get',
                        data: options.post,
                        dataType: 'text',
                        headers: options.headers
                    }).done(function (response) {
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
                        } else {
                            $(element).html(response);
                        }

                        if (options.onload) {
                            /**
                             * A 'onload' callback has been asked
                             */
                            options.onload();
                        }
                    }.bind(this)).fail(function (xhr) {
                        this.loading.stop();
                        var code = xhr.status;

                        // The page is not accessible for the user
                        var response;

                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch (e) {
                            response = {
                                message: xhr.responseText
                            };
                        }

                        if (code === 401) {
                            // The user is not connected, display the login form
                            this.dialog(this.getUri('login', {}, {
                                redirect: url,
                                code: code
                            }));

                            return;
                        }

                        this.notify('danger', response.message);
                    }.bind(this));
                }

                return new $.Deferred();
            }

            /**
             * Open a set of pages
             *
             * @param {Array} uris The uris to open, each one in a tab
             * @param {Function} onload The callback to execute when all the tabs are loaded
             * @returns {Promise} A promise reslved when all te tabs are open
             */

        }, {
            key: 'openLastTabs',
            value: function openLastTabs(uris) {
                var _this3 = this;

                // var loaded = 0;

                return $.when.apply(this, uris.map(function (uri) {
                    return _this3.load(uri, {
                        newtab: true
                    });
                })).done(function () {
                    _this3.loading.stop();

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

        }, {
            key: 'notify',
            value: function notify(level, message, options) {
                if (level === 'error') {
                    level = 'danger';
                }

                if (level === 'desktop') {
                    // this is a desktop notification
                    if (!('Notification' in window)) {
                        this.notify('success', message);
                    } else if (Notification.permission === 'granted') {
                        var notif = new Notification(message, options);
                    } else if (Notification.permission !== 'denied') {
                        // Ask for user permission to display notifications
                        Notification.requestPermission(function (permission) {
                            Notification.permission = permission;
                            this.notify(level, message, options);
                        }.bind(this));
                    }
                } else {
                    // Display an advert message in the application
                    this.notification.display = true;
                    this.notification.message = message;
                    this.notification.level = level;

                    if (level !== 'danger') {
                        this.notification.timeout = setTimeout(function () {
                            this.hideNotification();
                        }.bind(this), 5000);
                    }
                }
            }

            /**
             * Hide the displayed notification
             */

        }, {
            key: 'hideNotification',
            value: function hideNotification() {
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

        }, {
            key: 'dialog',
            value: function dialog(action, options) {
                var _this4 = this;

                options = options || {};

                this.dialogbox.display = false;
                this.dialogbox.content = '';

                if (action === 'close') {
                    return null;
                }

                // Load the content from an url
                this.loading.start();

                return $.ajax({
                    url: action,
                    type: 'get',
                    data: {
                        _dialog: true
                    }
                }).done(function (content) {
                    _this4.loading.stop();

                    // Page successfully loaded
                    _this4.dialogbox.display = true;
                    _this4.dialogbox.content = content;

                    if (options.onload) {
                        options.onload();
                    }

                    return content;
                }).fail(function (xhr) {
                    // Page load failed
                    var response;
                    _this4.loading.stop();

                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (err) {
                        response = {
                            message: xhr.responseText
                        };
                    }

                    _this4.notify('danger', response.message);
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

        }, {
            key: 'getUri',
            value: function getUri(method, args, queryString) {
                var route = null;

                if (method in this.routes) {
                    route = this.routes[method];
                } else {
                    for (var i in this.routes) {
                        if (this.routes[i].action === method) {
                            route = this.routes[i];
                            break;
                        }
                    }
                }

                if (!route) {
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

                if (queryString) {
                    url += '?' + $.param(queryString);
                }

                return this.conf.basePath + url;
            }
        }, {
            key: 'getRouteFromUri',


            /**
             * Get the route name corresponding to an URI
             *
             * @param    {string} uri - The uri to look the corresponding route for
             * @returns   {Object} The found route
             */
            value: function getRouteFromUri(uri) {
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
        }, {
            key: 'getRouteInformationFromUri',


            /**
             * Get the route corresponding to an URI. This method turns the route name and the path parameters
             * @param   {string} uri The uri to look the corresponding route for
             * @returns {Object}     The found route parameters, or null
             */
            value: function getRouteInformationFromUri(uri) {
                var _this5 = this;

                var path = uri.replace(/\/?\?.*$/, '');

                for (var i in this.routes) {
                    if (this.routes.hasOwnProperty(i)) {
                        var _ret = function () {
                            var regex = new RegExp('^' + _this5.routes[i].pattern + '$');
                            var match = path.match(regex);

                            if (path.match(regex)) {
                                var _ret2 = function () {
                                    var result = {
                                        name: i,
                                        data: {}
                                    };
                                    Object.keys(_this5.routes[i].where).forEach(function (key, index) {
                                        result.data[key] = match[index + 1];
                                    });

                                    return {
                                        v: {
                                            v: result
                                        }
                                    };
                                }();

                                if ((typeof _ret2 === 'undefined' ? 'undefined' : _typeof(_ret2)) === "object") return _ret2.v;
                            }
                        }();

                        if ((typeof _ret === 'undefined' ? 'undefined' : _typeof(_ret)) === "object") return _ret.v;
                    }
                }

                return null;
            }

            /**
             * Set the existing routes of the application
             *
             * @param {Object} routes The routes to set
             */

        }, {
            key: 'setRoutes',
            value: function setRoutes(routes) {
                this.routes = routes;
            }

            /**
             * Set the language of the application
             *
             * @param {string} language The language tag
             */

        }, {
            key: 'setLanguage',
            value: function setLanguage(language) {
                this.language = language;
            }

            /**
             * Set the root url of the application
             *
             * @param {string} url The root url to set
             */

        }, {
            key: 'setRootUrl',
            value: function setRootUrl(url) {
                this.rootUrl = url;
            }

            /**
             * Refresh the main menu
             */

        }, {
            key: 'refreshMenu',
            value: function refreshMenu() {
                var _this6 = this;

                $.get(this.getUri('refresh-menu'), function (response) {
                    _this6.menu.items = response;

                    _this6.notify('warning', Lang.get('main.main-menu-changed'));
                });
            }

            /**
             * Print a part of the page (or the whole page)
             *
             * @param {NodeElement} element The DOM element to print.
             *                              If not set or null, then this will print the whole page
             * @memberOf App
             */

        }, {
            key: 'print',
            value: function print(element) {
                if (!element) {
                    window.print();
                } else {
                    // Create a frame to wrap the content to print
                    var frame = document.createElement('iframe');

                    document.body.appendChild(frame);

                    // Add the content to the page
                    frame.contentDocument.body.innerHTML = element.outerHTML;
                    frame.style.display = 'none';

                    // Add the css
                    var style = document.createElement('link');

                    style.rel = 'stylesheet';
                    style.href = document.getElementById('theme-base-stylesheet').href;
                    style.type = 'text/css';
                    style.media = 'print';
                    style.onload = function () {
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

        }, {
            key: 'reloadRoutes',
            value: function reloadRoutes() {
                var _this7 = this;

                return $.getJSON(this.getUri('all-routes')).done(function (routes) {
                    _this7.setRoutes(routes);
                });
            }
        }]);

        return App;
    }(EMV);

    /**
     * The URI to return for non existing route
     *
     * @constant
     * @memberOf App
     */


    App.INVALID_URI = window.appConf.basePath + '/INVALID_URI';

    // Instanciate the application
    var app = new App();

    if (!window.app) {
        window.app = app;
    }

    app.start();

    return app;
});

require(['app']);