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
     * Initialize the application
     */
    public function init(){
        // Load the application configuration
        $this->singleton('conf', Conf::getInstance());

        // Load the application error Handler
        $this->singleton('errorHandler', ErrorHandler::getInstance());

        // Load the application logger
        $this->singleton('logger', Logger::getInstance());

        // Load the filesystem library
        $this->singleton('fs', FileSystem::getInstance());

        // Load the application session
        $this->singleton('session', Session::getInstance());

        // Load the application router
        $this->singleton('router', Router::getInstance());

        // Load the application HTTP request
        $this->singleton('request', Request::getInstance());

        // Load the application HTTP response
        $this->singleton('response', Response::getInstance());

        // Load the application cache
        $this->singleton('cache', Cache::getInstance());
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
}

/**
 * Throw this exception to force the script to finish properly
 *
 * @package Exceptions
 */
class AppStopException extends \Exception{

}
