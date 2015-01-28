<?php

require INCLUDES_DIR . 'constants.php';
require INCLUDES_DIR . 'functions.php';
require INCLUDES_DIR . 'autoload.php';
require INCLUDES_DIR . 'config.php';

define("ROOT_URL", Conf::get('rooturl') . '/');

if(Conf::has('db')){
    /*** Access to the OS database (MySQL) ***/   
    try{
        DB::add(MAINDB, Conf::get('db'));
        DB::get(MAINDB);
    }
    catch(DBException $e){
        // The database is not configured, redirect to the installation
        exit(DEBUG_MODE ? $e->getMessage() : Lang::get('main.connection-error'));
    }
}

/*** Get the session system ***/
define('SESSION_SYSTEM', Conf::get('session.system'));

/*** Constants depending to the options ***/
define("LANGUAGE", Option::get('main.language'));
    
/*** Timezone ***/
define("TIMEZONE", Option::get('main.timezone'));
date_default_timezone_set(TIMEZONE);

/*** Define the main paths ***/
define('THEMES_ROOT_URL', ROOT_URL . 'themes/');
define('PLUGINS_ROOT_URL', ROOT_URL . 'plugins/');

define('UPLOADS_ROOT_URL', ROOT_URL . 'uploads/');
define('UPLOADS_PLUGINS_URL', UPLOADS_ROOT_URL . 'plugins/');
define('UPLOADS_THEMES_URL', UPLOADS_ROOT_URL . 'themes/');

?>