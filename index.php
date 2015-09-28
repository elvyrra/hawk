<?php
namespace Hawk;

/*** Initialize the application ***/
define('SCRIPT_START_TIME', microtime(true));

define('ROOT_DIR', __DIR__ . '/');
define('INCLUDES_DIR', ROOT_DIR . 'includes/');

require ROOT_DIR . 'start.php';

Log::debug('script has been Initialized');

(new Event('process-start'))->trigger();


/*** Initialize the plugins ***/
$plugins = Conf::has('db') ? array_merge(Plugin::getMainPlugins(), Plugin::getActivePlugins()) : array(Plugin::get('main'), Plugin::get('install'));
foreach($plugins as $plugin){	
	if(is_file($plugin->getStartFile())){
		include $plugin->getStartFile();
	}
}

if(!Conf::has('db') && (Router::getUri() === '/' || Router::getUri() === 'index.php')) {
    Log::debug('Hawk is not installed yet, redirect to install process page');
    Response::redirect(Router::getUri('install'));
    return;
}

(new Event('before-routing'))->trigger();

/*** Compute the routage ***/
Router::route();

Log::debug('end of script');
$event = new Event('process-end', array(
	'output' => Response::get(), 
	'execTime' => microtime(true) - SCRIPT_START_TIME
));
$event->trigger();

Response::set($event->getData('output'));

/*** Return the response to the client ***/
Response::end();