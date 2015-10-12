<?php
/**
 * Util.class.php
 * @author Elvyrra SAA
 * @license MIT
 */

namespace Hawk;

/**
 * This trait contains utilities method that cen be used anywhere in the core
 * @package Utils
 */
trait Utils{
    /**
     * Display variables for development debug
     * @param mixed $var The variable to display
     * @param bool $exit if set to true, exit the script
     */
    public static function debug($var, $exit = false){
        if(DEBUG_MODE){     
            $trace = debug_backtrace()[0];
            echo "<pre>" ,
                    var_export($var, true) , PHP_EOL ,
                    $trace['file'], ":", $trace['line'], PHP_EOL,
                "</pre>";
                
            if($exit){
                exit;
            }
        }
    }


    /**
     * Get the current Hawk version
     * @return string The current version
     */
    public static function getHawkVersion(){
        return file_get_contents(ROOT_DIR . 'version.txt');
    }


    /**
     * Map an array data to the object that use this trait
     * @param array $array The data to map to the object
     * @param Object $object The object to map. If not set, get $this in the execution context of the method
     */
    public function map($array, $object = null){
        if($object === null){
            $object = $this;
        }
        foreach($array as $key => $value){
            $object->$key = $value;
        }
    }
}