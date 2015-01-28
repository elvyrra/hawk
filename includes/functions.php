<?php

function debug($var, $exit = false){
	if(DEBUG_MODE){		
		$trace = debug_backtrace()[0];
		echo "<pre>".var_export($var, true)."\n".
			$trace['file'].":".$trace['line']."\n".
			"</pre>";
		if($exit)
			exit;
	}
}