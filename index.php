<?php

namespace Hawk;

/*** Initialize the application ***/
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

$app = App::getInstance();

$app->init();

$app->on('after.route', function ($event) use($app) {
	$req = $app->request;
    $route = $req->route;

    if(!$app->conf->has('db') && $route->getName() === 'index') {
        // The application is not installed yet
        $app->logger->notice('Hawk is not installed yet, redirect to install process page');
        $app->response->redirectToRoute('install');
        return;
    }
    elseif($app->conf->has('db') && in_array($route->getName(), array('install', 'install-settings'))) {
        $app->response->redirectToRoute('index');
        return;
    }
});

$app->logger->debug('Script has been initialized');

$app->addMiddleware(new \Hawk\Middlewares\Configuration)
    ->addMiddleware(new \Hawk\Middlewares\StartPlugins)
    ->addMiddleware(new \Hawk\Middlewares\StartTheme)
    ->addMiddleware(new \Hawk\Middlewares\Route)
    ->addMiddleware(new \Hawk\Middlewares\CheckRequestAccess)
    ->addMiddleware(new \Hawk\Middlewares\ExecuteController);

$app->run();
