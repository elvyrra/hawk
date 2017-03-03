<?php

namespace Hawk\Middlewares;

/**
 * This middleware initialize and configure the application
 */
class ExecuteCron extends \Hawk\Middleware {
    const NAME = 'execute-cron';

    /**
     * Execute the middleware
     * @param  Request $req  The HTTP request
     * @param  Response $res  The HTTP response
     */
    public function execute($req, $res) {
        global $argv;

        $file = $argv[1];

        include $file;
    }
}