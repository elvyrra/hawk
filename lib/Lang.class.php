<?php
/**********************************************************************
 *    						Lang.class.php
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
class Lang{
	const DEFAULT_LANGUAGE = 'fr';	
	public static $langs = array(); // This array contains all textdomains
	
	/*____________________________________________________________________
                  
                        Load a text domain
    ____________________________________________________________________*/
    public static function load($plugin, $filename, $once = false){		
        if(!$once || !isset(self::$langs[$plugin])){			
            $fullpath = $filename . '.' . self::DEFAULT_LANGUAGE . '.lang';
			
			// load the keys of the default language
			$cache = new FileCache($fullpath, 'lang', 'php');
			
			if($cache->isCached()){
				// Use the cache file to avoid to parse ini file
				self::$langs[$plugin] = include $cache->get();
			}			
			else{
				// Parse the ini file containing language keys
				self::$langs[$plugin] = parse_ini_file($fullpath);
				
				// register the parsed file in cache
				$cache->set('<?php return '.var_export(self::$langs[$plugin], true).";");				
			}
            if(LANGUAGE != self::DEFAULT_LANGUAGE){
				// load the keys of the asked language
				$fullpath = $filename . '.' . LANGUAGE . '.lang';
				$cache = new FileCache($fullpath, 'lang', 'php');
				if(basename($fullpath) == "global..php")
					debug(LANGUAGE);
				if($cache->isCached()){
					// Use the cache file to avoid to parse ini file
					$language = include $cache->get();
					self::$langs[$plugin] = array_merge_recursive(self::$langs[$plugin], $language);
				}
				else{
					// Parse the ini file containing language keys
					$language = parse_ini_file($fullpath);
				    self::$langs[$plugin] = array_merge_recursive(self::$langs[$plugin], $language);
					
					// Put the parsed array in the cache file
					$cache->set('<?php return '.var_export($language, true).";");	
				}
            }
        }
	}
	
	
	public static function exists($langKey){
		list($plugin, $key) = explode('.', $langKey);
        
		// get the label(s)
		return isset(self::$langs[$plugin][$key]);
	}
    
    /*____________________________________________________________________
                  
                        Return a key translation
    ____________________________________________________________________*/
    public static function get($langKey, $param = array(), $number = 0){
		list($plugin, $key) = explode('.', $langKey);
        
		// get the label(s)
		$labels = isset(self::$langs[$plugin][$key]) ? self::$langs[$plugin][$key] : null;
		
        if($labels !== null){
            if(is_array($labels)){
				// Multiple values are affected to this key (singular / plural)
				if((int) $number > 1)
					// Get the plural of the language key
                    $label = isset($labels[$number]) ? $labels[$number] : $labels['p'];
                else
					// Get the singular of the language key
                    $label = isset($labels[$number]) ? $labels[$number] : $labels['s'];
            }
            else
				// The language key is a single string
                $label = $labels;			
			
			if(!empty($param)){
				// Replace parameters into the language key
				return str_replace(array_map(function($key){ return '{'.$key.'}';}, array_keys($param)), $param, $label);
			}
			else
				return $label;
        }
        else{
            return $langKey;
        }
    }
 
}