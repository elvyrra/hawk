<?php
/**
 * ErrorHandler.php
 */

namespace Hawk;

/**
 * This class defines the errors and exceptions handlers
 * @package Core
 */
class ErrorHandler {	

	/**
	 * Handler an error
	 * @param int $no The error number
	 * @param string $str The error message
	 * @param string $file The file that throwed the error
	 * @param int $line The line in the file where the error was throwed
	 * @param array $context All the variables in the error context
	 */
	public function error($no, $str, $file, $line, $context){	
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
			break;

			case E_STRICT :
				$level = "info";
				$icon = 'info-circle';
				$title = "PHP Strict";
			break;

			case E_DEPRECATED :
			case E_USER_DEPRECATED :
				$level = "info";
				$icon = 'info-circle';
				$title = "PHP Deprecated";
			break;

			case E_USER_WARNING :
			case E_WARNING :
				$level = "warning";
				$icon = "exclamation-triangle";
				$title = "PHP Warning";
			break;

			default:
				$level = "danger";
				$icon = "exclamation-circle";
				$title = "PHP Error";
			break;
		}

		$param = array(
			'level' => $level,
			'icon' => $icon,
			'title' => $title,
			'message' => $str,
			'trace' => array(
				array('file' => $file, 'line' => $line)
			)
		);

		if(!App::response()->getContentType() === "json"){			
			App::response()->setBody($param);
			App::response()->end();
		}
		else{
			echo View::make(Theme::getSelected()->getView('error.tpl'), $param);
		}
	}


	/**
	 * Handle an non catched exception
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

		if(App::response()->getContentType() === "json"){
			App::response()->setBody($param);
		}
		else{				
			App::response()->setBody(View::make(Theme::getSelected()->getView('error.tpl'), $param));
		}
		App::response()->end();
	}

	/**
	 * Handle fatal errors
	 */
	public function fatalError(){
		$error = error_get_last();

		if( $error !== NULL) {
		    $errno   = $error["type"];
		    $errfile = $error["file"];
		    $errline = $error["line"];
		    $errstr  = $error["message"];

		    $this->error($errno, $errstr, $errfile, $errline, array());
		}
	}
	
}