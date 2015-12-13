<?php
/**
 * Util.php
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
     * Serialize a version number as integer. This method can be used to compare two versions
     */
    public static function getSerializedVersion($version){
        $digits = explode('.', $version);
        $number = '';
        foreach(range(0,3) as $i) {
            if(empty($digits[$i])){
                $digits[$i] = '00';
            }
            $number .= str_pad($digits[$i], 2, '0', STR_PAD_LEFT);
        }
        return $number;
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