<?php

namespace Hawk\Middlewares;

use \Hawk\DB as DB;
use \Hawk\Option as Option;
use \Hawk\Lang as Lang;
use \Hawk\DatabaseSessionHandler as DatabaseSessionHandler;

/**
 * This middleware initialize and configure the application
 */
class Configuration extends \Hawk\Middleware {
	const NAME = 'configuration';

	/**
	 * Execute the middleware
	 * @param  Request $req  The HTTP request
	 * @param  Response $res  The HTTP response
	 */
	public function execute($req, $res) {
		$app = $this->app;

		if(is_file(INCLUDES_DIR . 'config.php')) {
		    include INCLUDES_DIR . 'config.php';
		}

		if(!defined('HAWK_SITE_URL')) {
		    define('HAWK_SITE_URL', 'http://www.hawk-app.com');
		}

		define("ROOT_URL",  preg_replace('#/?$#', '', (string) $app->conf->get('rooturl')));

		// Define the base path of the site URIs, base on the root Url
		define('BASE_PATH', str_replace('//', '/', parse_url(ROOT_URL, PHP_URL_PATH)));

		// Define the main paths
		define('STATIC_URL', ROOT_URL . '/static/');
		define('THEMES_ROOT_URL', STATIC_URL . 'themes/');
		define('PLUGINS_ROOT_URL', STATIC_URL . 'plugins/');

		if($app->conf->has('db')) {
		    // Access to the OS database (MySQL)
		    try{
		        DB::add(MAINDB, $app->conf->get('db.maindb'));

		        $app->singleton('db', DB::get(MAINDB));
		    }
		    catch(DBException $e){
		        // The database is not configured, redirect to the installation
		        exit(DEBUG_MODE ? $e->getMessage() : Lang::get('main.connection-error'));
		    }

		    // Configure the session engine
		    session_set_save_handler(new DatabaseSessionHandler());
		}

		// Open the session
		$app->session->init();

		// Constants depending to the options
		if($req && $req->getCookies('language')) {
		    define('LANGUAGE', $req->getCookies('language'));
		}
		elseif($app->conf->has('db')) {
		    if(!$app->isCron && $app->session->getUser()->getProfileData('language')) {
		        define('LANGUAGE', $app->session->getUser()->getProfileData('language'));
		    }
		    elseif(Option::get('main.language')) {
		        define('LANGUAGE', Option::get('main.language'));
		    }
		}
		else{
		    define('LANGUAGE', Lang::DEFAULT_LANGUAGE);
		}

		// Timezone
		define("TIMEZONE", $app->conf->has('db') && Option::get('main.timezone') ? Option::get('main.timezone')  : DEFAULT_TIMEZONE);
		date_default_timezone_set(TIMEZONE);

		$app->logger->debug('Script has been initialized');
	}
}