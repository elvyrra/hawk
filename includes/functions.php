<?php
/**
 * @author Elvyrra SAS
 */


/**
 * Display variables for development debug
 * @param mixed $var The variable to display
 * @param bool $exit if set to true, exit the script
 */
function debug($var, $exit = false){
	if(DEBUG_MODE){		
		$trace = debug_backtrace()[0];
		echo "<pre>" ,
				var_export($var, true) , PHP_EOL ,
				$trace['file'], ":", $trace['line'], PHP_EOL,
			"</pre>";
			
		if($exit){
			exit;
		}
	}
}
