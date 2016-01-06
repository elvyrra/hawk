<?php
/**
 * Singleton.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class describes the general behavior of singleton classes, sucha as Request, Response, ...
 * @package Core
 */
class Singleton{
    /**
     * The singleton instance
     */
    protected static $instance;

    /**
     * The constrcutor
     */
    protected function __construct(){}

    /**
     * Get the singleton instance
     */
    public static function getInstance(){
        if(!isset(static::$instance)){
            $class = get_called_class();
            static::$instance = new $class();
        }

        return static::$instance;
    }
}