<?php
if(ini_get('display_errors')){
	set_error_handler('ErrorHandler::error', error_reporting());

	set_exception_handler('ErrorHandler::exception');
}

