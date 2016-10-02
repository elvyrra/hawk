<?php
namespace Hawk;

try {
    /*** Initialize the application ***/
    define('SCRIPT_START_TIME', microtime(true));

    define('ROOT_DIR', __DIR__ . '/');
    define('INCLUDES_DIR', ROOT_DIR . 'includes/');

    include ROOT_DIR . 'start.php';

    App::logger()->debug('script has been Initialized');

    (new Event('process-start'))->trigger();

    /*** Initialize the plugins ***/
    $plugins = App::conf()->has('db') ? Plugin::getActivePlugins() : array(Plugin::get('main'), Plugin::get('install'));
    foreach($plugins as $plugin) {
        if(is_file($plugin->getStartFile())) {
            include $plugin->getStartFile();
        }
    }

    /*** Initialize the theme ***/
    if(is_file(Theme::getSelected()->getStartFile())) {
        include Theme::getSelected()->getStartFile();
    }

    (new Event('before-routing'))->trigger();

    /*** Execute action just after routing ***/
    Event::on(
        'after-routing', function ($event) {
            $route = $event->getData('route');

            if(!App::conf()->has('db') && App::request()->getUri() == App::router()->getUri('index')) {
                // The application is not installed yet
                App::logger()->notice('Hawk is not installed yet, redirect to install process page');
                App::response()->redirectToAction('install');
                return;
            }
        }
    );

    /*** Compute the routage ***/
    App::router()->route();
}
catch(HTTPException $err) {
    App::response()->setStatus($err->getStatusCode());

    $response = array(
        'message' => $err->getMessage(),
        'details' => $err->getDetails()
    );

    if(App::request()->getWantedType() === 'json') {
        App::response()->setContentType('json');
        App::response()->setBody($response);
    }
    else {
        App::response()->setBody($response['message']);
    }
}
catch(AppStopException $e){
}

// Finish the script
App::logger()->debug('end of script');
$event = new Event(
    'process-end', array(
    'output' => App::response()->getBody(),
    'execTime' => microtime(true) - SCRIPT_START_TIME
    )
);
$event->trigger();

App::response()->setBody($event->getData('output'));

/*** Return the response to the client ***/
App::response()->end();