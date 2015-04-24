<?php
if(ini_get('display_errors')){	
	set_error_handler(function($no, $str, $file, $line, $context){			
		switch($no){
			case E_NOTICE :
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
				array('file' => $file, 'line' => $line, 'function' => '', 'args' => array())
			)
		);

		if(!Response::getType() === "json"){			
			exit(json_encode($param));
		}
		else{
			echo View::make(Plugin::get('main')->getView('error.tpl'), $param);
		}

	}, E_USER_ERROR | E_USER_WARNING);

	function exception_handler($e){
		$param = array(
			'level' => 'error',
			'icon' => 'excalamation-circle',
			'title' => gettype($e),
			'message' => $e->getMessage(),
			'trace' => $e->getTrace()
		);
		
		if(Response::getType() === "json"){
			echo json_encode($param);
		}
		else{
			echo View::make(Plugin::get('main')->getView('error.tpl'), $param);
		}
		exit;
	}
	set_exception_handler('exception_handler');
}