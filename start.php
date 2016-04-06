<?php
namespace Hawk;

// Get the core constants
require INCLUDES_DIR . 'constants.php';

// Get the constants customized by developer for the application
if(!is_file(INCLUDES_DIR . 'custom-constants.php')) {
    touch(INCLUDES_DIR . 'custom-constants.php');
}
require INCLUDES_DIR . 'custom-constants.php';

// Load the autoload system
require INCLUDES_DIR . 'autoload.php';

// Initialize the application and singletons
App::getInstance()->init();

// Load the application configuration
if(is_file(INCLUDES_DIR . 'config.php')) {
    include INCLUDES_DIR . 'config.php';
}

if(!defined('HAWK_SITE_URL')) {
    define('HAWK_SITE_URL', 'http://www.hawk-app.com');
}

// Load the error handlers
require INCLUDES_DIR . 'error-handler.php';

// Define the ROOT URL removing the last "/"
define("ROOT_URL",  preg_replace('#/?$#', '', (string) App::conf()->get('rooturl')));

// Define the base path of the site URIs, base on the root Url
define('BASE_PATH', str_replace('//', '/', parse_url(ROOT_URL, PHP_URL_PATH)));

/*** Define the main paths ***/
define('STATIC_URL', ROOT_URL . '/static/');
define('THEMES_ROOT_URL', STATIC_URL . 'themes/');
define('PLUGINS_ROOT_URL', STATIC_URL . 'plugins/');

if(App::conf()->has('db')) {
    /*** Access to the OS database (MySQL) ***/
    try{
        DB::add(MAINDB, App::conf()->get('db.maindb'));

        App::getInstance()->singleton('db', DB::get(MAINDB));
    }
    catch(DBException $e){
        // The database is not configured, redirect to the installation
        exit(DEBUG_MODE ? $e->getMessage() : Lang::get('main.connection-error'));
    }
}

/*** Open the session ***/
if(App::conf()->has('db')) {
    session_set_save_handler(new DatabaseSessionHandler());
}
session_set_cookie_params((int) App::conf()->get('session.lifetime'), '/');
session_start();
App::session()->init();

/*** Constants depending to the options ***/
if(App::request()->getCookies('language')) {
    define('LANGUAGE', App::request()->getCookies('language'));
}
elseif(App::conf()->has('db')) {
    if(App::session()->getUser()->getProfileData('language')) {
        define('LANGUAGE', App::session()->getUser()->getProfileData('language'));
    }
    elseif(Option::get('main.language')) {
        define('LANGUAGE', Option::get('main.language'));
    }
}
else{
    define('LANGUAGE', Lang::DEFAULT_LANGUAGE);
}

/*** Timezone ***/
define("TIMEZONE", App::conf()->has('db') && Option::get('main.timezone') ? Option::get('main.timezone')  : DEFAULT_TIMEZONE);
date_default_timezone_set(TIMEZONE);
