<?php
/**
 * Util.php
 *
 * @author  Elvyrra SAA
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This trait contains utilities method that cen be used anywhere in the core
 *
 * @package Utils
 */
trait Utils{
    /**
     * Display variables for development debug
     *
     * @param mixed $var  The variable to display
     * @param bool  $exit if set to true, exit the script
     */
    public static function debug($var, $exit = false){
        if(DEBUG_MODE) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            echo "<pre>" ,
                    var_export($var, true) , PHP_EOL ,
                    $trace['file'], ":", $trace['line'], PHP_EOL,
                "</pre>";

            if($exit) {
                exit;
            }
        }
    }


    /**
     * Serialize a version number as integer.
     * Example :
     * <code>
     * <?php
     *      echo Utils::getSerializedVersion('1.15.12'); // Output "01151200"
     * ?>
     * </code>
     *
     * @param string $version The version number to serialize, in the format gg.rr.cc(.pp)
     *
     * @return string The serialized version
     */
    public static function getSerializedVersion($version){
        $digits = explode('.', $version);
        $number = '';
        foreach(range(0, 3) as $i) {
            if(empty($digits[$i])) {
                $digits[$i] = '00';
            }
            $number .= str_pad($digits[$i], 2, '0', STR_PAD_LEFT);
        }
        return $number;
    }

    /**
     * Map an array data to the object that use this trait
     *
     * @param array  $array  The data to map to the object
     * @param Object $object The object to map. If not set, get $this in the execution context of the method
     */
    public function map($array, $object = null){
        if($object === null) {
            $object = $this;
        }
        foreach($array as $key => $value){
            $object->$key = $value;
        }
    }
}