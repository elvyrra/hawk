<?php

namespace Hawk\Middlewares;

use \Hawk\Plugin as Plugin;
/**
 * This middleware initialize and configure the application
 */
class StartPlugins extends \Hawk\Middleware {
	const NAME = 'start-plugins';

	/**
	 * Execute the middleware
	 * @param  Request $req  The HTTP request
	 * @param  Response $res  The HTTP response
	 */
	public function execute($req, $res) {
		$plugins = $this->app->conf->has('db') ?
			Plugin::getActivePlugins() :
			array(
				Plugin::get('main'),
				Plugin::get('install')
			);

	    foreach($plugins as $plugin) {
	        $plugin->start();
	    }
	}
}