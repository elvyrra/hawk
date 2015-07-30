<?php
/**
 * Log.class.php
 */

/**
 * This class is used to log data in /logs directory. You can use it to log the action of the users on the application, for example to make stats.
 */
class Log{
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    const MAX_FILE_SIZE = 200000;

    /**
     * The Log instances, representing each one a level
     */
    private static $instances = array();

    /**
     * The basename of the file wherelogs are written
     */
    private $basename,

    /**
     * The filename where logs are written
     */
    $filename, 

    /**
     * The resource use to write the logs during script execution
     */
    $resource, 

    /**
     * The level of the log ('debug', 'info', 'notice', 'warning', 'error')
     */
    $level;

    private function __construct($level){
        if(!in_array($level, self::getLevels())) {
            throw new LogException('The level ' . $level . ' does not exists for logs');
        }
        $this->basename = $level . '.log';
        $this->filename = LOG_DIR . $this->basename;

        if(is_file($this->filename)){
            // The file already exists
            if(filesize($this->filename) > self::MAX_FILE_SIZE){
                // Archive the last file and create a new one

                // rename all archives already existing (keep only last 9 archives)
                $archives = array_reverse(glob($this->filename . '.*.zip'));
                foreach($archives as $archive){
                    $basename = basename($archives);
                    preg_match('/^' . preg_quote($this->basename, '/') . '\.(\d+)\.zip$/', $basename, $match);
                    if($match[1] > 9){
                        unlink($archive);
                    }
                    else{
                        rename($archive, $this->filename . '.' . ($match[1] + 1) . '.zip');
                    }
                }

                // Create the new archive
                $zip = ZipArchive::open($this->filename . '.0.zip', ZipArchive::CREATE);
                $zip->addFile($this->filename);
                $zip->close();

                $this->resource = fopen($this->filename, 'w+');
            }
            else{
                // The file size is not reached, append new logs to the file
                $this->resource = fopen($this->filename, 'a+');
            }
        }
        else{
            // The log file does not exists yet, create it
            $this->resource = fopen($this->filename, 'w+');
        }

        // Add an event on the end of the script to close the resource
        EventManager::on('process-end', function($event){            
            fclose($this->resource);
        });
    }

    /**
     * Get the available levels of log
     */
    private static function getLevels() {
        $object = new ReflectionClass(__CLASS__);

        $levels = $object->getConstants();
        unset($levels['MAX_FILE_SIZE']);

        return $levels;
    }


    /**
     * Write log
     */
    private function write($message){
        $trace = debug_backtrace();
        $trace = (object) $trace[1];

        $data = array(
            'date' => date('Y-m-d H:i:s'),
            'uri' => Request::uri(),
            'ip' => Request::clientIp(),
            'trace' => $trace->file . ':' . $trace->line,
            'message' => $message,
        );

        $input =  implode(' - ', $data) . PHP_EOL;        
        fwrite($this->resource, $input);
    }

    /**
     * Get a log instance
     */
    private static function getInstance($level){
        if(!isset(self::$instances[$level])){
            self::$instances[$level] = new self($level);
        }

        return self::$instances[$level];
    }

    /**
     * Log info data. Use this function to log user action like form submission
     */
    public static function info($message){
        self::getInstance(self::LEVEL_INFO)->write($message);
    }

    /**
     * Log debug data. this function is used to log script execution steps
     */
    public static function debug($message){
        self::getInstance(self::LEVEL_DEBUG)->write($message);
    }

    /**
     * Log notice data. This function is used to log anormal non blocking usage
     */
    public static function notice($message){
        self::getInstance(self::LEVEL_NOTICE)->write($message);
    }

    /**
     * Log warning data. this function is used to log actions that didn't work because of user bad action (eg form badly completed)
     */
    public static function warning($message){
        self::getInstance(self::LEVEL_WARNING)->write($message);
    }

    /**
     * Log error data. This function is used to log actions that didn't work because of a script error 
     */
    public static function error($message){
        self::getInstance(self::LEVEL_ERROR)->write($message);
    }
}

class LogException extends Exception{}