<?php
/**
 * ErrorHandler.php
 *
 * @author  Elvyrra
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class defines the errors and exceptions handlers
 *
 * @package Core
 */
final class ErrorHandler extends Singleton{

    /**
     * The error handler instance
     */
    protected static $instance;

    /**
     * Handler an error
     *
     * @param int    $no      The error number
     * @param string $str     The error message
     * @param string $file    The file that throwed the error
     * @param int    $line    The line in the file where the error was throwed
     * @param array  $context All the variables in the error context
     */
    public function error($no, $str, $file, $line, $context) {
        if (!(error_reporting() & $no)) {
            // This error code is not in error_reporting
            return;
        }

        switch($no){
            case E_NOTICE :
            case E_USER_NOTICE:
                $level = "info";
                $icon = 'info-circle';
                $title = "PHP Notice";
                App::logger()->notice($str);
                break;

            case E_STRICT :
                $level = "info";
                $icon = 'info-circle';
                $title = "PHP Strict";
                App::logger()->notice($str);
                break;

            case E_DEPRECATED :
            case E_USER_DEPRECATED :
                $level = "info";
                $icon = 'info-circle';
                $title = "PHP Deprecated";
                App::logger()->notice($str);
                break;

            case E_USER_WARNING :
            case E_WARNING :
                $level = "warning";
                $icon = "exclamation-triangle";
                $title = "PHP Warning";
                App::logger()->warning($str);
                break;

            default:
                $level = "danger";
                $icon = "exclamation-circle";
                $title = "PHP Error";
                App::logger()->error($str);
                break;
        }

        $param = array(
            'level' => $level,
            'icon' => $icon,
            'title' => $title,
            'message' => $str,
            'trace' => array(
                array(
                    'file' => $file,
                    'line' => $line
                )
            )
        );


        echo View::make(Theme::getSelected()->getView('error.tpl'), $param);
    }


    /**
     * Handle an non catched exception
     *
     * @param Exception $e The throwed exception
     */
    public function exception($e){
        $param = array(
            'level' => 'danger',
            'icon' => 'excalamation-circle',
            'title' => get_class($e),
            'message' => $e->getMessage(),
            'trace' => $e->getTrace()
        );

        App::logger()->error($e->getMessage());
        if(App::request()->getWantedType() === "json") {
            throw new InternalErrorException($e->getMessage());
        }
        else{
            throw new InternalErrorException(View::make(Theme::getSelected()->getView('error.tpl'), $param));
        }

    }

    /**
     * Handle fatal errors
     */
    public function fatalError(){
        $error = error_get_last();

        if($error !== null) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];

            $this->error($errno, $errstr, $errfile, $errline, array());
        }
    }


    /**
     * Start error handler
     */
    public function start() {
        if(ini_get('display_errors')) {
            set_error_handler(array($this, 'error'), error_reporting());
            register_shutdown_function(array($this, 'fatalError'));

            set_exception_handler(array($this, 'exception'));
        }
    }

    /**
     * Manage Http exceptions
     * @param  HTTPException $err The thrown exception
     * @param  Request       $req The HTTP request
     * @param  response      $res The HTTP response
     */
    public function manageHttpError(HTTPException $err, $req, $res) {
        $res->setStatus($err->getStatusCode());

        $response = array(
            'message' => $err->getMessage(),
            'details' => $err->getDetails()
        );

        if($req->getWantedType() === 'json') {
            $res->setContentType('json');
            $res->setBody($response);
        }
        else {
            $res->setBody($response['message']);
        }
    }
}
