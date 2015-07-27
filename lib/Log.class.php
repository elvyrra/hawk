<?php
/**
 * Log.class.php
 */

/**
 * This class is used to log data in /logs directory
 */
class Log{
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice'
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    private static $instances = array();

    private function __construct($level){
        if(!isset(self::$instances)) {
            
        }
    }
}