<?php

namespace Hawk\Middlewares;

use \Hawk\Theme as Theme;

/**
 * This middleware initialize and configure the application
 */
class StartTheme extends \Hawk\Middleware {
	const NAME = 'start-theme';

	/**
	 * Execute the middleware
	 * @param  Request $req  The HTTP request
	 * @param  Response $res  The HTTP response
	 */
	public function execute($req, $res) {
		Theme::getSelected()->start();
	}
}