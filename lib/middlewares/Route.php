<?php

namespace Hawk\Middlewares;

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

        if($route) {
            $req->route = $route;

            return;
        }

        // No matching route
        $this->app->logger->warning('The URI ' . $req->getUri() . ' has not been routed');
        throw new \Hawk\PageNotFoundException();
	}
}