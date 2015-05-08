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
	const ORIGIN_CACHE_FILE = CACHE_DIR . 'lang-file-paths.php';

	public static $langs = array(); // This array contains all textdomains
	
	private static $originCache = array();

	/**
	 * Load a language file 
	 * @param {string} $plugin1 - The first plugin to load
	 * @param {string} $plugin2 - ...
	 */
	public static function load(){
		

		$plugins = func_get_args();
		
		foreach($plugins as $plugin){
			if(!isset(self::$langs[$plugin])){
				self::$langs[$plugin] = array();
				/*
				 * First Load the default language
				 */
				$cache = CACHE_LANG_DIR . $plugin . '.' . self::DEFAULT_LANGUAGE . '.php';
				$origin = self::getOriginFile($plugin, self::DEFAULT_LANGUAGE);

				if($origin){
					if(!is_file($cache) || filemtime($origin) > filemtime($cache)){
						self::parse($origin, $cache);
					}

					self::$langs[$plugin] = include $cache;
				}
				
				if(LANGUAGE !== self::DEFAULT_LANGUAGE){

					$cache = CACHE_LANG_DIR . $plugin . '.' . LANGUAGE . '.php';
					$origin = self::getOriginFile($plugin, LANGUAGE);

					if($origin){
						if(!is_file($cache) || filemtime($origin) > filemtime($cache)){
							self::parse($origin, $cache);
						}

						self::$langs[$plugin] = include $cache;
					}
				}
			}
		}
	}

	/**
	 * Find the origin language file
	 * @param {string} $plugin - The plugin to search
	 * @param {string} $lang - The language in the file
	 * @return {string} - The path of the origin language file
	 */
	private static function getOriginFile($plugin, $lang){
		if(is_file(self::ORIGIN_CACHE_FILE) && empty(self::$originCache)){
			self::$originCache = include self::ORIGIN_CACHE_FILE;
		}

		if(isset(self::$originCache["$plugin.$lang"])){
			// the file is registered in the cache
			return self::$originCache["$plugin.$lang"];
		}

		// The file is not present in the cache, search it
		$cmd = 'find ' . ROOT_DIR . ' -name "' . $plugin . '.' . $lang . '.lang"';
		exec($cmd, $output);		

		if(!empty($output)){
			// a file was found
			$file = $output[0];

			// register it in the cache
			self::$originCache["$plugin.$lang"] = $file;
			
			return $file;
		}
		return null;
	}


	/**
	 * Parse origin file 
	 */
	public static function parse($origin, $target){
		if(!is_dir(CACHE_LANG_DIR)){
			mkdir(CACHE_LANG_DIR);
		}
		file_put_contents($target, '<?php return ' . var_export(parse_ini_string(file_get_contents($origin, true)), true) . ';');
	}


	public static function saveCache(){
		file_put_contents(self::ORIGIN_CACHE_FILE, '<?php return ' . var_export(self::$originCache, true) . ';');
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