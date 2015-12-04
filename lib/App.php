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
    private static $instance;

    private function __construct(){}

    public function getInstance(){
        if(isset(self::$instance)){
            return self::$instance;            
        }

        self::$instance = new self;
        return self::$instance;
    }

    public function init(){
        $this->singleton('conf', new Conf());
        $this->singleton('errorHandler', new ErrorHandler());
        $this->singleton('request', new Request());
        $this->singleton('response', new Response());        

        $this->singleton('router', new Router());
    }

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
