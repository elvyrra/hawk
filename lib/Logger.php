<?php
/**
 * Logger.php
 *
 * @author  Elvyrra
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to log data in /logs directory.
 * You can use it to log the action of the users on the application, for example to make stats.
 *
 * @package Core
 */
final class Logger extends Singleton{
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    const MAX_FILE_SIZE = 20480000;
    const MAX_FILES_BY_LEVEL = 9;

    /**
     * The logger instance
     *
     * @var Logger
     */
    protected static $instance;

    /**
     * The file resources
     */
    private $resources = array();


    /**
     * Open a log file
     *
     * @param string $level The level of the log file
     */
    private function open($level){
        $basename = $level . '.log';
        $dirname = App::conf()->get('log.dir') ? App::conf()->get('log.dir') : LOG_DIR;
        $filename = $dirname . $basename;

        if(is_file($filename) && filesize($filename) >= self::MAX_FILE_SIZE) {
            // Archive the last file and create a new one

            // rename all archives already existing (keep only last 9 archives)
            $archives = array_reverse(glob($filename . '.*.zip'));
            foreach($archives as $archive){
                preg_match('/^' . preg_quote($basename, '/') . '\.(\d+)\.zip$/', basename($archive), $match);
                if($match[1] > self::MAX_FILES_BY_LEVEL) {
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

            unlink($filename);
        }
        $this->resources[$level] = fopen($filename, 'a+');
    }

    /**
     * Write log
     *
     * @param string $level   The log level : 'debug', 'info', 'notice', 'warning', 'error'
     * @param string $message The message to write
     */
    private function write($level, $message) {
        if(empty($this->resources[$level])) {
            $this->open($level);
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $trace = (object) $trace[1];

        $app = App::getInstance();
        if(!$app->isCron) {
            $request = $app->request;

            $method = $request->getMethod();
            $ip = $request->clientIp();
            $uri = $request->getUri();
            $uid = $request->uid;
        }
        else {
            global $argv;

            $method = 'NA';
            $ip = 'cron';
            $uri = $argv[1];
            $uid = $app->uid;
        }

        $data = array(
            'date' => date_create()->format('Y-m-d H:i:s'),
            'requestId' => $uid,
            'method' => $method,
            'clientIp' => $ip,
            'uri' => $uri,
            'file' => $trace->file,
            'line' => $trace->line,
            'message' => $message,
        );

        $input =  json_encode($data, JSON_UNESCAPED_SLASHES);
        fwrite($this->resources[$level], $input . PHP_EOL);
    }

    /**
     * Log info data.
     * Use this function to log user action like form submission
     *
     * @param string $message The message to write
     */
    public function info($message){
        $this->write(self::LEVEL_INFO, $message);
    }

    /**
     * Log debug data.
     * This function is used to log script execution steps
     *
     * @param string $message The message to write
     */
    public function debug($message){
        $this->write(self::LEVEL_DEBUG, $message);
    }

    /**
     * Log notice data.
     * This function is used to log anormal non blocking usage
     *
     * @param string $message The message to write
     */
    public function notice($message){
        $this->write(self::LEVEL_NOTICE, $message);
    }

    /**
     * Log warning data.
     * This function is used to log actions that didn't work because of user bad action (eg form badly completed)
     *
     * @param string $message The message to write
     */
    public function warning($message){
        $this->write(self::LEVEL_WARNING, $message);
    }

    /**
     * Log error data.
     * This function is used to log actions that didn't work because of a script error
     *
     * @param string $message The message to write
     */
    public function error($message){
        $this->write(self::LEVEL_ERROR, $message);
    }
}