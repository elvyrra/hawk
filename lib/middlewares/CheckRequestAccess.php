<?php

namespace Hawk\Middlewares;

/**
 * This middleware initialize and configure the application
 */
class CheckRequestAccess extends \Hawk\Middleware {
    const NAME = 'check-request-access';

    /**
     * Execute the middleware
     * @param  Request $req  The HTTP request
     * @param  Response $res  The HTTP response
     */
    public function execute($req, $res) {
        $route = $req->route;

        if(!$route->isAccessible()) {
            // The route is not accessible
            App::logger()->warning(sprintf(
                'A user with the IP address %s tried to access %s without the necessary privileges',
                $req->clientIp(),
                $req->getUri()
            ));

            if(!App::session()->isLogged()) {
                throw new UnauthorizedException();
            }
            else {
                throw new ForbiddenException();
            }
        }

        return true;
    }
}