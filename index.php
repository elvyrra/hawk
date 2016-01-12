<?php
namespace Hawk;

try{
    /*** Initialize the application ***/
    define('SCRIPT_START_TIME', microtime(true));

    define('ROOT_DIR', __DIR__ . '/');
    define('INCLUDES_DIR', ROOT_DIR . 'includes/');

    require ROOT_DIR . 'start.php';

    App::logger()->debug('script has been Initialized');

    (new Event('process-start'))->trigger();

    /*** Initialize the plugins ***/
    $plugins = App::conf()->has('db') ? array_merge(Plugin::getMainPlugins(), Plugin::getActivePlugins()) : array(Plugin::get('main'), Plugin::get('install'));
    foreach($plugins as $plugin){
    	if(is_file($plugin->getStartFile())){
    		include $plugin->getStartFile();
    	}
    }

    Event::on('after-routing', function($event){
        $route = $event->getData('route');
        $controllerClass = $route->getActionClassname();
        $controller = $controllerClass::getInstance();


        if(!App::conf()->has('db') && App::request()->getUri() == App::router()->getUri('index')) {
            App::logger()->debug('Hawk is not installed yet, redirect to install process page');
            App::response()->redirectToAction('install');
            return;
        }

        if(!App::request()->isAjax() && App::request()->getMethod() == 'get' && ! in_array($controller->getPlugin()->getName(), array('main', 'install'))) {

            $event->setData('route', App::router()->getRouteByName('index'));
        }
    });


    /*** Initialize the theme ***/
    if(is_file(Theme::getSelected()->getStartFile())){
        include Theme::getSelected()->getStartFile();
    }

    (new Event('before-routing'))->trigger();

    /*** Compute the routage ***/
    App::router()->route();
}
catch(AppStopException $e){}

// Finish the script
App::logger()->debug('end of script');
$event = new Event('process-end', array(
	'output' => App::response()->getBody(),
	'execTime' => microtime(true) - SCRIPT_START_TIME
));
$event->trigger();

App::response()->setBody($event->getData('output'));

/*** Return the response to the client ***/
App::response()->end();