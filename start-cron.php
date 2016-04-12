<?php
namespace Hawk;

define('SCRIPT_START_TIME', microtime(true));

define('ROOT_DIR', __DIR__ . '/');
define('INCLUDES_DIR', ROOT_DIR . 'includes/');

// Get the core constants
require INCLUDES_DIR . 'constants.php';

// Get the constants customized by developer for the application
if(!is_file(INCLUDES_DIR . 'custom-constants.php')) {
    touch(INCLUDES_DIR . 'custom-constants.php');
}
require INCLUDES_DIR . 'custom-constants.php';

// Load the autoload system
require INCLUDES_DIR . 'autoload.php';

function getallheaders(){ return array(); }

// Initialize the application and singletons
$app = App::getInstance();
$app->init();

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

/*** Access to the OS database (MySQL) ***/
try{
    DB::add(MAINDB, App::conf()->get('db.maindb'));

    $app->singleton('db', DB::get(MAINDB));
}
catch(DBException $e){
    // The database is not configured, redirect to the installation
    exit(DEBUG_MODE ? $e->getMessage() : Lang::get('main.connection-error'));
}
/*** Open the session ***/
session_set_save_handler(new DatabaseSessionHandler());
session_set_cookie_params((int) App::conf()->get('session.lifetime'), '/');
session_start();
App::session()->init();

/*** Constants depending to the options ***/
define('LANGUAGE', Option::get('main.language'));

/*** Timezone ***/
define("TIMEZONE", Option::get('main.timezone'));
date_default_timezone_set(TIMEZONE);

/*** Initialize the plugins ***/
$plugins = App::conf()->has('db') ? Plugin::getActivePlugins() : array(Plugin::get('main'), Plugin::get('install'));
foreach($plugins as $plugin){
    if(is_file($plugin->getStartFile())) {
        include $plugin->getStartFile();
    }
}

$filename = $argv[1];

include $filename;