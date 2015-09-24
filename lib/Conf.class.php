<?php
/**
 * Conf.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class is used to get and set the application base configuration
 * @package Core
 */
class Conf{
	/**
	 * The application configuration cache
	 * @var array
	 */
	private static $conf;
	
	/**
	 * Get a configuration value 
	 * @param string $option The name of the configuration parameter to get
	 * @return mixed The configuration value
	 */
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
	

	/**
	 * Set a configuration parameter
	 * @param string $option The name of the parameter to set
	 * @param mixed $value The value to set
	 */
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
	

	/**
	 * Check if a configuration parameter exists
	 * @param string $option The parameter name to find
	 * @return boolean True if the parameter exists in the application configuration, false else
	 */
	public static function has($option){
		$value = self::get($option);
		return $value !== null;
	}

}