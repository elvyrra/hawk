<?php
/*** Initialize the application ***/
define('SCRIPT_START_TIME', microtime(true));

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

EventManager::trigger(new Event('process-start'));

/*** Initialize the plugins ***/
$plugins = array_merge(Plugin::getMainPlugins(), Plugin::getActivePlugins());
foreach($plugins as $plugin){	
	if(is_file($plugin->getStartFile())){
		include $plugin->getStartFile();
	}
}

EventManager::trigger(new Event('before-routing'));

/*** Compute the routage ***/
Router::route();

$event = new Event('process-end', array(
	'output' => phpQuery::newDocument(Response::get()), 
	'execTime' => microtime(true) - SCRIPT_START_TIME
));
EventManager::trigger($event);

Response::set($event->getData('output')->htmlOuter());

/*** Return the response to the client ***/
Response::end();