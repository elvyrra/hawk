<?php

/**
 * App.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class is used to generate the application singletons
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
    }


    /**
     * Create an application singleton
     */
    public function singleton($name, $instance){
        $this->$name = $instance;
    }

    /**
     * Call a singleton
     */
    public static function __callStatic($method, $arguments){
        if(isset(self::$instance->$method)){
            return self::$instance->$method;
        }
        else{
            throw new \Exception('The application singleton "' . $method . '" has not been initiated');
        }
    }
}
