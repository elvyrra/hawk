<?php

/**
 * App.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to generate the application singletons. It is loaded at script start, and initialize the following
 * singletons : conf, errorHandler, logger, fs (FileSystem), session, router, request, response, cache and db
 *
 * @package Core
 */
final class App extends Singleton{
    /**
     * The application instance
     */
    protected static $instance;

    /**
     * The list of the application middlewares
     * @var array
     */
    private $middlewares = array();

    /**
     * Defines if the application is run as a script
     * @var boolean
     */
    public $isCron = false;

    /**
     * Initialize the application
     */
    public function init(){
        // Load the application configuration
        $this->singleton('conf', Conf::getInstance());

        // Load the application error Handler
        if($this->conf->get('errorHandler')) {
            $this->singleton('errorHandler', $this->conf->get('errorHandler'));
        }
        else {
            $this->singleton('errorHandler', ErrorHandler::getInstance());
        }

        // Load the application logger
        $this->singleton('logger', Logger::getInstance());

        // Load the filesystem library
        $this->singleton('fs', FileSystem::getInstance());

        // Load the system library
        $this->singleton('system', System::getInstance());

        // Load the application session
        $this->singleton('session', Session::getInstance());

        if(!$this->isCron) {
            // Load the application HTTP request
            $this->singleton('request', Request::getInstance());

            // Load the application HTTP response
            $this->singleton('response', Response::getInstance());
        } else {
            $this->uid = uniqid();
        }

        // Load the application router
        $this->singleton('router', Router::getInstance());

        // Load the application cache
        $this->singleton('cache', Cache::getInstance());

        // Start the error handler
        $this->errorHandler->start();
    }

    /**
     * Check id the application is installed
     */
    public static function isInstalled() {
        return self::conf()->has('db');
    }


    /**
     * Create an application singleton
     *
     * @param string $name     The singleton name, that will running by App::{$name}().
     *                         For example, if $name = 'db', the singleton will be accessible by App::db();
     * @param object $instance The singleton instance
     */
    public function singleton($name, $instance){
        $this->$name = $instance;
    }

    /**
     * Call a singleton
     *
     * @param string $method    The method name, corresponding to the singleton name
     * @param array  $arguments The singleton arguments (not used, but mandatory when overriding __callStatic method)
     */
    public static function __callStatic($method, $arguments){
        if(isset(self::$instance->$method)) {
            return self::$instance->$method;
        }
        else{
            throw new \Exception('The application singleton "' . $method . '" has not been initiated');
        }
    }

    /**
     * Add a middleware to the application
     * @param  Middleware $middleware The middleware to add
     * @return App        The application itself, to chain actions
     */
    public function addMiddleware($middleware) {
        $middleware->app = $this;

        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Run the application
     */
    public function run() {
        if(!$this->isCron) {
            $req = self::request();
            $res = self::response();
        }
        else {
            $req = null;
            $res = null;
        }

        try {
            foreach($this->middlewares as $middleware) {
                // Send an event before the middleware execution
                $this->trigger('before.' . $middleware::NAME, array(
                    'req' => $req,
                    'res' => $res
                ));

                $middleware->execute($req, $res, $this);

                // Send an event after the middleware execution
                $this->trigger('after.' . $middleware::NAME, array(
                    'req' => $req,
                    'res' => $res
                ));
            }
        }
        catch(HTTPException $err) {
            if(!$this->isCron) {
                try {
                    $this->errorHandler->manageHttpError($err, $req, $res);
                }
                catch(AppStopException $err){}
            }
            else {
                echo $err->getMessage();
                exit;
            }
        }
        catch(\Hawk\AppStopException $err) {
        }

        if(!$this->isCron) {
            $this->finalize($req, $res);
        }
    }

    /**
     * Listen to an event
     * @param  string $name      The event name
     * @param  callable $handler The action to execute when the event is triggered. This function gets one parameter,
     *                           the event itself
     */
    public function on($name, $handler) {
        Event::on($name, $handler);
    }

    /**
     * Trigger an event
     * @param  string $name The event name
     * @param  array  $data  The event data
     */
    public function trigger($name, $data) {
        $event = new Event($name, $data);

        $event->trigger();
    }

    /**
     * Finalize the script
     */
    public function finalize($req, $res) {
        // Finish the script
        self::logger()->debug('end of script');

        $event = new Event('process-end', array(
            'output' => $res->getBody(),
            'execTime' => microtime(true) - SCRIPT_START_TIME
        ));

        $event->trigger();

        $res->setBody($event->getData('output'));

        /*** Return the response to the client ***/
        $res->end();
    }
}