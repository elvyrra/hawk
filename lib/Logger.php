<?php
/**
 * Logger.php
 */

namespace Hawk;

/**
 * This class is used to log data in /logs directory. You can use it to log the action of the users on the application, for example to make stats.
 * @package Core
 */
final class Logger extends Singleton{
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    const MAX_FILE_SIZE = 204800;
    const MAX_FILES_BY_LEVEL = 9;

    /**
     * The logger instance
     * @var Logger
     */
    protected static $instance;

    /**
     * The file resources
     */
    private $resources = array();

    
    /**
     * Open a log file
     * @param string $level The level of the log file
     */
    private function open($level){        
        $basename = $level . '.log';
        $filename = LOG_DIR . $basename;

        if(is_file($filename)){
            // The file already exists
            if(filesize($filename) >= self::MAX_FILE_SIZE){
                // Archive the last file and create a new one

                // rename all archives already existing (keep only last 9 archives)
                $archives = array_reverse(glob($filename . '.*.zip'));
                foreach($archives as $archive){
                    preg_match('/^' . preg_quote($basename, '/') . '\.(\d+)\.zip$/', basename($archive), $match);
                    if($match[1] > self::MAX_FILES_BY_LEVEL){
                        unlink($archive);
                    }
                    else{
                        rename($archive, $filename . '.' . ($match[1] + 1) . '.zip');
                    }
                }

                // Create the new archive
                $zip = new \ZipArchive;
                $zip->open($filename . '.0.zip', \ZipArchive::CREATE);
                $zip->addFile($filename);
                $zip->close();

            }            
        }
        $this->resources[$level] = fopen($filename, 'a+');        
    }

    /**
     * Write log
     * @param string $level The log level : 'debug', 'info', 'notice', 'warning', 'error'
     * @param string $message The message to write
     */
    private function write($level, $message){
        if(empty($this->resources[$level])) {
            $this->open($level);
        }

        $trace = debug_backtrace();
        $trace = (object) $trace[1];

        $data = array(
            'date' => date('Y-m-d H:i:s'),
            'uri' => App::request()->getUri(),
            'ip' => App::request()->clientIp(),
            'trace' => $trace->file . ':' . $trace->line,
            'message' => $message,
        );

        $input =  implode(' - ', $data) . PHP_EOL;        
        fwrite($this->resources[$level], $input);
    }

    /**
     * Log info data. Use this function to log user action like form submission
     * @param string $message The message to write
     */
    public function info($message){
        $this->write(self::LEVEL_INFO, $message);        
    }

    /**
     * Log debug data. this function is used to log script execution steps
     * @param string $message The message to write
     */
    public function debug($message){
        $this->write(self::LEVEL_DEBUG, $message); 
    }

    /**
     * Log notice data. This function is used to log anormal non blocking usage
     * @param string $message The message to write
     */
    public function notice($message){
        $this->write(self::LEVEL_NOTICE, $message); 
    }

    /**
     * Log warning data. this function is used to log actions that didn't work because of user bad action (eg form badly completed)
     * @param string $message The message to write
     */
    public function warning($message){
        $this->write(self::LEVEL_WARNING, $message); 
    }

    /**
     * Log error data. This function is used to log actions that didn't work because of a script error 
     * @param string $message The message to write
     */
    public function error($message){
        $this->write(self::LEVEL_ERROR, $message); 
    }
}