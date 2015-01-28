<?php
/**********************************************************************
 *    						Conf.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
	
class Conf{
	private static $conf;
	const FILE = "conf.json";
	
	public static function get($option = ''){
		if(!isset(self::$conf)){
			return null;
		}
		
		if(empty($option))
			return self::$conf;
		else{		
			$fields = explode('.', $option);
			$tmp = self::$conf;
			foreach($fields as $field){
				$tmp = $tmp[$field];		
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
	
	public static function update(){
		return file_put_contents(ROOT_DIR . self::FILE, json_encode(self::$conf, JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));	
	}
	
	public static function has($option){
		$value = self::get($option);
		return $value !== null;
	}

}