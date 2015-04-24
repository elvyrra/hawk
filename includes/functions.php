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

function q($selector){
	return DOMQuery::getInstance()->find($selector);	
}

function button($param){
	extract($param);	
	
	/*** Adapt parameters ***/
	unset($param['label'], $param['class'], $param['textStyle'], $param['icon']);
	if(empty($param['type']))
		$param['type'] = "button";
	
	/*** Set the attributes of the button ***/	
	if(!preg_match("!\bbtn-\w+\b!", $class))
		$class .= " btn-default";
	
	/*** Set the attribute and text to the span inside the button ***/
	if(!$icon)
		$class .= " btn-text-only";
	
	if(!$label)
		$class .= " btn-icon-only";
	
	$param = array_map(function($v){return str_replace('"', '\\"', $v); }, $param);
	
	return View::make(ThemeManager::getSelected()->getView("button.tpl") ,array(
		'class' => $class,
		'param' => $param,
		'icon' => $icon,
		'label' => $label,
		'textStyle' => $textStyle		
	));
}

set_exception_handler(function($e){
	echo "<pre>",
			$e->getMessage(), "\n";
	foreach($e->getTrace() as $i => $trace){
		echo "#$i {$trace['file']}:{$trace['line']} => {$trace['function']}(".implode(",", $trace['args']).")\n";
	}
	echo "</pre>";	
});

function get_array_value($array){
	$indexes = array_slice(func_get_args(), 1);
	$tmp = $array;
	foreach($indexes as $index){
		if(!isset($tmp[$index])){
			return null;
		}
		$tmp = $tmp[$index];		
	}
	
	return $tmp;
}


