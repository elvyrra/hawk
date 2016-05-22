<?php
/**
 * ViewException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the View exceptions
 *
 * @package Exceptions
 */
class ViewException extends \Exception{
    /**
     * Error type : source file not found
     */
    const TYPE_FILE_NOT_FOUND = 1;

    /**
     * Error type : view evaluation failed
     */
    const TYPE_EVAL = 2;

    /**
     * Constructor
     *
     * @param int       $type     The type of exception
     * @param string    $file     The source file that caused this exception
     * @param Exception $previous The previous exception that caused that one
     */
    public function __construct($type, $file, $previous = null){
        $code = $type;
        switch($type){
            case self::TYPE_FILE_NOT_FOUND:
                $message = "Error creating a view from template file $file : No such file or directory";
                break;

            case self::TYPE_EVAL:
                $trace = array_map(
                    function ($t) {
                        return $t['file'] . ':' . $t['line'];
                    }, $previous->getTrace()
                );

                $message = "An error occured while building the view from file $file : " . $previous->getMessage() . PHP_EOL . implode(PHP_EOL, $trace);
                break;
        }

        parent::__construct($message, $code, $previous);
    }
}