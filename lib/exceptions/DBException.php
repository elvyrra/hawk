<?php
/**
 * DBException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class manages the exceptions throwed by DB class
 *
 * @package Exceptions
 */
class DBException extends \Exception{
    const CONNECTION_ERROR = 1;
    const QUERY_ERROR = 2;

    /**
     * Constructor
     *
     * @param string    $message  The exception message
     * @param int       $code     The exception $code
     * @param string    $value    The exception content
     * @param Exception $previous The previous exception that throwed that one
     */
    public function __construct($message, $code, $value, $previous = null){
        switch($code){
            case self::CONNECTION_ERROR :
                $message = "Impossible to connect to Database Server : $value, $message";
                break;

            case self::QUERY_ERROR:
                $message = "An error was detected : $message in the Database Query : $value";
                App::logger()->error($message);
                break;

        }

        parent::__construct($message, $code, $previous);
    }
}