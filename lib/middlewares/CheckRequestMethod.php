<?php

namespace Hawk\Middlewares;

use \Hawk\BadMethodException as BadMethodException;

/**
 * This middleware initialize and configure the application
 */
class CheckRequestMethod extends \Hawk\Middleware {
	const NAME = 'check-request-method';

	/**
	 * Execute the middleware
	 * @param  Request $req  The HTTP request
	 * @param  Response $res  The HTTP response
	 */
	public function execute($req, $res) {
		$route = $req->route;

        // Check if the route is accessible with the current method
        if(!$route->isCallableBy($req->getMethod())) {
            throw new BadMethodException($route->url, $req->getMethod());
        }

        return true;
	}
}