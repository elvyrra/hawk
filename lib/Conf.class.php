<?php

	
class Conf{
	private static $conf;
	
	public static function get($option = ''){
		if(!isset(self::$conf)){
			return null;
		}
		
		if(empty($option)){
			return self::$conf;
		}
		else{		
			$fields = explode('.', $option);
			$tmp = self::$conf;
			foreach($fields as $field){
				if(isset($tmp[$field])){
					$tmp = $tmp[$field];		
				}
				else{
					return null;
				}
			}
			return $tmp;
		}	
	}
	
	public static function set($option, $value = null){
		if($value == null){
			self::$conf = $option;		
		}
		else{
			$fields = explode('.', $option);
			$tmp = & self::$conf;
			foreach($fields as $field){
				$tmp = &$tmp[$field];		
			}
			$tmp = $value;
		}
	}
	
	public static function has($option){
		$value = self::get($option);
		return $value !== null;
	}

}