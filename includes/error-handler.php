<?php
if(ini_get('display_errors')){
	set_error_handler('\\Hawk\\ErrorHandler::error', error_reporting());

	set_exception_handler('\\Hawk\\ErrorHandler::exception');
}

