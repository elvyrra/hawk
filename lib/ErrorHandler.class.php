<?php
/**
 * ErrorHandler.class.php
 */

/**
 * This class defines the errors and exceptions handlers
 */

class ErrorHandler {

	public static function error($no, $str, $file, $line, $context){			
		switch($no){
			case E_NOTICE :
			case E_STRICT :
			case E_DEPRECATED :
			case E_USER_DEPRECATED :
				$level = "info";
				$icon = 'info-circle';
				$title = "PHP Notice";
			break;

			case E_USER_WARNING :
			case E_WARNING :
				$level = "warning";
				$icon = "exclamation-triangle";
				$title = "PHP Warning";
			break;

			case E_ERROR :
			case E_USER_ERROR :
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

		if(!Response::getType() === "json"){			
			Response::set(json_encode($param));
			Response::end();
		}
		else{
			echo View::make(ThemeManager::getSelected()->getView('error.tpl'), $param);
		}
	}

	public static function exception($e){
		if(ini_get('display_errors')){			
			$param = array(
				'level' => 'danger',
				'icon' => 'excalamation-circle',
				'title' => get_class($e),
				'message' => $e->getMessage(),
				'trace' => $e->getTrace()
			);

			if(Response::getType() === "json"){
				Response::set(json_encode($param));
			}
			else{				
				Response::set(View::make(ThemeManager::getSelected()->getView('error.tpl'), $param));
			}
		}
		else{
			Response::setHttpCode(500);
		}
		Response::end();
	}
}