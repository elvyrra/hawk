<?php

namespace Hawk\Middlewares;

/**
 * This middleware initialize and configure the application
 */
class ExecuteController extends \Hawk\Middleware {
    const NAME = 'execute-controller';

    /**
     * Execute the middleware
     * @param  Request $req  The HTTP request
     * @param  Response $res  The HTTP response
     */
    public function execute($req, $res) {
        $route = $req->route;

        // The route authentications are validated
        list($classname, $method) = explode(".", $route->action);

        // call a controller method
        $this->currentController = $classname::getInstance($route->getData());
        $this->app->logger->debug(sprintf(
            'URI %s has been routed => %s::%s',
            $req->getUri(),
            $classname,
            $method
        ));

        // Set the controller result to the HTTP response
        $res->setBody($this->currentController->$method());
    }
}