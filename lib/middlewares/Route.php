<?php

namespace Hawk\Middlewares;

use \Hawk\PageNotFoundException as PageNotFoundException;
use \Hawk\BadMethodException as BadMethodException;

/**
 * This middleware initialize and configure the application
 */
class Route extends \Hawk\Middleware {
    const NAME = 'route';

    /**
     * Execute the middleware
     * @param  Request $req  The HTTP request
     * @param  Response $res  The HTTP response
     */
    public function execute($req, $res) {
        $path = str_replace(BASE_PATH, '', parse_url($req->getUri(), PHP_URL_PATH));
        $route = $this->app->router->route($path);
        $matchingRoutes = array();

        foreach($this->app->router->getRoutes() as $route) {
            if($route->match($path)) {
                if($route->isCallableBy($req->getMethod())) {
                    $req->route = $route;

                    return;
                }
                else {
                    $matchingRoutes[] = $route;
                }
            }
        }

        if(!empty($matchingRoutes)) {
            // The path matches at least a route, but not with the current method
            throw new BadMethodException($req->uri, $req->getMethod());
        }

        // No matching route
        $this->app->logger->warning('The URI ' . $req->getUri() . ' has not been routed');

        throw new PageNotFoundException();
    }
}