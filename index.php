<?php
namespace Hawk;

/*** Initialize the application ***/
define('SCRIPT_START_TIME', microtime(true));

define('ROOT_DIR', __DIR__ . '/');
define('INCLUDES_DIR', ROOT_DIR . 'includes/');

require ROOT_DIR . 'start.php';

Log::debug('script has been Initialized');

(new Event('process-start'))->trigger();

if(!App::conf()->has('db') && (Router::getUri() === '/' || Router::getUri() === 'index.php')) {
    Log::debug('Hawk is not installed yet, redirect to install process page');
    App::response()->redirect(Router::getUri('install'));
    return;
}

/*** Initialize the plugins ***/
$plugins = App::conf()->has('db') ? array_merge(Plugin::getMainPlugins(), Plugin::getActivePlugins()) : array(Plugin::get('main'), Plugin::get('install'));
foreach($plugins as $plugin){	
	if(is_file($plugin->getStartFile())){
		include $plugin->getStartFile();
	}
}

/*** Initialize the theme ***/
if(is_file(Theme::getSelected()->getStartFile())){
    include Theme::getSelected()->getStartFile();
}

(new Event('before-routing'))->trigger();

/*** Compute the routage ***/
Router::route();

Log::debug('end of script');
$event = new Event('process-end', array(
	'output' => App::response()->getBody(), 
	'execTime' => microtime(true) - SCRIPT_START_TIME
));
$event->trigger();

App::response()->setBody($event->getData('output'));

/*** Return the response to the client ***/
App::response()->end();