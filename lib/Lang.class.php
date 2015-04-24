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
	const DEFAULT_LANGUAGE = 'en';
	public static $langs = array(); // This array contains all textdomains
	
	/**
	 * Load a language file 
	 * @param {string} $plugin1 - The first plugin to load
	 * @param {string} $plugin2 - ...
	 */
	public static function load(){
		$plugins = func_get_args();
		
		foreach($plugins as $plugin){
			if(!isset(self::$langs[$plugin])){
				/*
				 * First Load the default language
				 */
				$defaultLangFile = CACHE_LANG_DIR . $plugin . '.' . self::DEFAULT_LANGUAGE . '.php';
				if(NO_CACHE || !is_file($defaultLangFile)){
					Language::getByTag(self::DEFAULT_LANGUAGE)->generateCacheFiles();
				}

				if(is_file($defaultLangFile)){
					self::$langs[$plugin] = include $defaultLangFile;
				}

				if(LANGUAGE !== self::DEFAULT_LANGUAGE){
					$langFile = CACHE_LANG_DIR . $plugin . '.' . LANGUAGE . '.php';

					if(NO_CACHE || !is_file($langFile)){
						Language::getByTag(LANGUAGE)->generateCacheFiles();
					}

					if(is_file($langFile)){
						self::$langs[$plugin] = include $langFile;
					}
				}
			}
		}
	}


	/**
	 * Check if a language key exists 
	 * @param {String} $langKey - the key to check existence
	 */
	public static function exists($langKey){
		list($plugin, $key) = explode('.', $langKey);
        
		// get the label(s)
		return isset(self::$langs[$plugin][$key]);
	}
    
    /**
     * get the translation of a language key in the current language
     * @param {String} $langKey - The key to get the translation
     * @param {Array} $param - On associative array containing the variables value in the translation
     * @param {mixed} $number - A number describing the singular or plural version of the translation
     * @return {String} - The translation
     */
    public static function get($langKey, $param = array(), $number = 0){
		list($plugin, $key) = explode('.', $langKey);

		if(!isset(self::$langs[$plugin])){
			self::load($plugin);
		}
        
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
	

	/**
	 * Add language keys to Javascript
	 * @param {String} $key1 - The first key
	 * @param {String} $key2 .....
	 */
	public static function addKeysToJavascript(){
		$keys = func_get_args();
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($keys){
			
			$script = "";
			foreach($keys as $key){
				list($plugin, $langKey) = explode(".", $key);
				$script .= "Lang.set('$key', '" . addcslashes(self::get($key), "'") . "');";
			}
			
			pq("*:first")->before("<script type='text/javascript'> $script </script>");			
		});		
	}
}