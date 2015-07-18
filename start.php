<?php

require INCLUDES_DIR . 'constants.php';
require INCLUDES_DIR . 'custom-constants.php';
require INCLUDES_DIR . 'autoload.php';
if(!is_file(INCLUDES_DIR . 'config.php')){
	touch(INCLUDES_DIR . 'config.php');
}
require INCLUDES_DIR . 'config.php';
require INCLUDES_DIR . 'functions.php';
require INCLUDES_DIR . 'error-handler.php';

define("ROOT_URL", (string) Conf::get('rooturl') . '/');

/*** Define the main paths ***/
define('THEMES_ROOT_URL', ROOT_URL . 'themes/');
define('PLUGINS_ROOT_URL', ROOT_URL . 'plugins/');

define('USERFILES_ROOT_URL', ROOT_URL . 'userfiles/');
define('USERFILES_PLUGINS_URL', USERFILES_ROOT_URL . 'plugins/');
define('USERFILES_THEMES_URL', USERFILES_ROOT_URL . 'themes/');

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
define('SESSION_ENGINE', Conf::has('session.engine') ? Conf::get('session.engine') : DEFAULT_SESSION_ENGINE);

/*** Constants depending to the options ***/
define("LANGUAGE", Conf::has('db') && Option::get('main.language') ? Option::get('main.language') : Lang::DEFAULT_LANGUAGE);
    
/*** Timezone ***/
define("TIMEZONE", Conf::has('db') && Option::get('main.timezone') ? Option::get('main.timezone')  : DEFAULT_TIMEZONE);
date_default_timezone_set(TIMEZONE);



?>