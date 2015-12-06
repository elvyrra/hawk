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
class App{
    /**
     * The application instance
     */
    private static $instance;

    /**
     * Constructor
     */
    private function __construct(){}

    /**
     * Get the application instance
     */
    public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self;            
        }

        return self::$instance;
    }

    /**
     * Initialize the application
     */
    public function init(){
        // Load the application configuration
        $this->singleton('conf', Conf::getInstance());

        // Load the application error Handler
        $this->singleton('errorHandler', new ErrorHandler());
        
        // Load the application HTTP request
        $this->singleton('request', new Request());
        
        // Load the application HTTP response
        $this->singleton('response', new Response());        

        // Load the filesystem library
        $this->singleton('fs', new FileSystem());

        // Load the application logger
        $this->singleton('logger', Logger::getInstance());

        // Load the application router
        $this->singleton('router', Router::getInstance());
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
            throw new \Exception('The application singleton ' . $method . ' has not been initiated');
        }
    }
}
