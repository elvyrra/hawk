<?php

/*** Initialize the application ***/
define('ROOT_DIR', __DIR__ . '/');
define('INCLUDES_DIR', ROOT_DIR . 'includes/');

require ROOT_DIR . 'start.php';

/*** Open the session ***/
$sessionInterface = ucfirst(SESSION_SYSTEM)."Session";
if(!empty($sessionInterface)){
	$handler = new $sessionInterface();
	session_set_save_handler($handler);
	session_start();
}

/*** Initialize the plugins ***/
$dirs = array(PLUGINS_DIR, MAIN_PLUGINS_DIR);
foreach($dirs as $dir){
	foreach(glob($dir.'*/start.php') as $file)
		include $file;
}

/*** Load the theme ***/
$theme = ThemeManager::getSelected();

/*** Compute the routage ***/
Router::route();

/*** Save the autoload cache ***/
Autoload::saveCache();

/*** Return the response to the client ***/
Response::end();