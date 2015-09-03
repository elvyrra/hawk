<?php
/**
 * Lang.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

/**
 * This class is used to manage translations
 * @package Core
 */
class Lang{

	const DEFAULT_LANGUAGE = 'en';
	const ORIGIN_CACHE_FILE = 'lang-file-paths.php';
	const CACHE_DIR = 'lang/';
	const TRANSLATIONS_DIR = 'admin/translations/';

	/**
	 * The language keys with their translations
	 * @var array
	 */
	private static $langs = array(), 

	/**
	 * The currently used language
	 * @var string
	 */
	$usedLanguage = '',
	
	/**
	 * The cache containing the source files paths
	 * @var array
	 */
	$originCache = array();

	/**
	 * The plugin of the language file
	 * @var string
	 */
	private $plugin, 

	/**
	 * The language of the language file
	 * @var string
	 */
	$lang, 

	/**
	 * The source file
	 * @var string
	 */
	$originFile, 

	/**
	 * The path of the file containing the custom translations 
	 */
	$translatedFile, 

	/**
	 * The path of the PHP cache file 
	 */
	$cacheFile;

	/**
	 * Constructor
	 * @param string $plugin The plugin of the file
	 * @param string $lang The language of the file
	 */
	private function __construct($plugin, $lang){
		$this->plugin = $plugin;
		$this->lang = $lang;

		$this->originFile  = $this->getOriginFile();
		$this->translatedFile = $this->getTranslatedFile();
		$this->cacheFile = $this->getCacheFile();
	}


	/**
	 * Find the origin language file
	 * @return string The path of the origin language file
	 */
	private function getOriginFile(){
		if(is_file(CACHE_DIR . self::ORIGIN_CACHE_FILE) && empty(self::$originCache)){
			self::$originCache = include CACHE_DIR . self::ORIGIN_CACHE_FILE;
		}

		if(isset(self::$originCache["$this->plugin.$this->lang"])){
			// the file is registered in the cache
			return self::$originCache["$this->plugin.$this->lang"];
		}

		// The file is not present in the cache, search it. We use the method Autoload::find that already performs this action		
		foreach(array(MAIN_PLUGINS_DIR, PLUGINS_DIR) as $dir){
			$files = FileSystem::find($dir, $this->plugin . '.' . $this->lang . '.lang', FileSystem::FIND_FILE_ONLY);
			if(!empty($files)){
				$file = $files[0];

				// register it in the cache
				self::$originCache["$this->plugin.$this->lang"] = $file;
				
				return $file;
			}
		}
		return null;
	}


	/**
	 * Find the translated file in userfiles directory
	 * @return string path The path of the file
	 */
	private function getTranslatedFile(){		
		return USERFILES_PLUGINS_DIR . self::TRANSLATIONS_DIR . $this->plugin . '.' . $this->lang . '.lang';
	}

	/**
	 * Find the cache file, containing the PHP version of the language files
	 * @return string path The path of the cache file
	 */
	private function getCacheFile(){
		return CACHE_DIR . self::CACHE_DIR . $this->plugin . '.' . $this->lang . '.php';
	}


	/**
	 * Parse a language file 
	 * @param string The file to parse
	 * @return array The language keys of the language file
	 */
	private function parse($file){		
		return is_file($file) ? parse_ini_string(file_get_contents($file)) : array();
	}


	/**
	 * Make the cache file
	 */
	private function build(){				
		if(!is_file($this->cacheFile) || (is_file($this->originFile) && filemtime($this->cacheFile) < filemtime($this->originFile)) || (is_file($this->translatedFile) && filemtime($this->cacheFile) < filemtime($this->translatedFile))){
			$data = array_merge($this->parse($this->originFile), $this->parse($this->translatedFile));
			if(!is_dir(CACHE_DIR . self::CACHE_DIR)){
				mkdir(CACHE_DIR . self::CACHE_DIR, 0755);
			}
			file_put_contents($this->cacheFile, '<?php return ' . var_export($data, true) . ';' );
		}
	}


	
	/**
	 * Load a language file 
	 * @param string $plugin The plugin to load
	 * @param string $language The language to get the translations in
	 * @param string $force If set to true, force to reload the translations
	 */
	private static function load($plugin, $language = LANGUAGE, $force = false){
		if(!isset(self::$langs[$plugin]) || $force){
			Log::debug('Reload keys for plugin ' . $plugin . ' and for language ' . $language);
			self::$langs[$plugin] = array();

			$instance = new self($plugin, self::DEFAULT_LANGUAGE);
			$instance->build();
			self::$langs[$plugin] = include $instance->cacheFile;

			if($language !== self::DEFAULT_LANGUAGE){
				$instance = new self($plugin, $language);
				$instance->build();
				$translations = include $instance->cacheFile;
				if(!is_array($translations)){
					$translations = array();
				}

				self::$langs[$plugin] = array_merge(self::$langs[$plugin], $translations);
			}
		}
	}


	/**
	 * Get the translations of a language file
	 * @param string $plugin The plugin to load
	 * @param string $language The language to get the translations in
	 * @param string $force If set to true, force to reload the translations
	 */
	public static function keys($plugin, $language = LANGUAGE, $reload = false){
		if(!isset(self::$langs[$plugin]) || $reload || $language != self::$usedLanguage){
			self::load($plugin, $language, $reload);
		}

		return self::$langs[$plugin];
	}


	/**
	 * Save the cache fil containing the origin paths
	 */
	public static function saveOriginCache(){
		file_put_contents(CACHE_DIR . self::ORIGIN_CACHE_FILE, '<?php return ' . var_export(self::$originCache, true) . ';');
	}




	/**
	 * Check if a language key exists 
	 * @param string $langKey The key to check existence
	 */
	public static function exists($langKey){
		list($plugin, $key) = explode('.', $langKey);
        
		// get the label(s)
		if(!isset(self::$langs[$plugin])){			
			self::load($plugin);			
		}
		return isset(self::$langs[$plugin][$key]);
	}



    
    /**
     * get the translation of a language key in the given language
     * @param string $langKey The key to get the translation
     * @param array $param On associative array containing the variables value in the translation
     * @param mixed $number A number describing the singular or plural version of the translation
     * @param string $language The language to get the translation. By default the current language
     * @return string The translation
     */
    public static function get($langKey, $param = array(), $number = 0, $language = LANGUAGE){
		$tmp = explode('.', $langKey);
		if(count($tmp) != 2){
			return $langKey;
		}
		
		list($plugin, $key) = explode('.', $langKey);

		if(!isset(self::$langs[$plugin]) || $language != self::$usedLanguage){						
			self::load($plugin, $language, true);
		}
		self::$usedLanguage = $language;
        
		// get the label(s)
		$labels = isset(self::$langs[$plugin][$key]) ? self::$langs[$plugin][$key] : null;
		
        if($labels !== null){
            if(is_array($labels)){
				// Multiple values are affected to this key (singular / plural)
				if((int) $number > 1){
					// Get the plural of the language key
                    $label = isset($labels[$number]) ? $labels[$number] : (isset($labels['p']) ? $labels['p'] : $langKey);
                }
                else{
					// Get the singular of the language key
                    $label = isset($labels[$number]) ? $labels[$number] : (isset($labels['s']) ? $labels['s'] : $langKey);
                }
            }
            else{
				// The language key is a single string
                $label = $labels;			
            }
			
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
	 * @param string $key1 The first key
	 * @param string $key2 .....
	 */
	public static function addKeysToJavascript(){
		$keys = func_get_args();
			
		$script = "";
		foreach($keys as $key){
			list($plugin, $langKey) = explode(".", $key);
			$script .= "Lang.set('$key', '" . addcslashes(self::get($key), "'") . "');";
		}
		
		Router::getCurrentController()->addJavaScriptInline($script);			
	}



	/**
	 * Get the translations data the user customized on the interface
	 * @param string $plugin The plugin name
	 * @param string $language The language tag
	 */
	public static function getUserTranslations($plugin, $language){
		$lang = new self($plugin, $language);
		$file = $lang->getTranslatedFile();
		return is_file($file) ? parse_ini_string(file_get_contents($file)) : array();
	}

	/**
	 * Save translated data the user customized on the interface
	 * @param string $plugin The plugin name
	 * @param string $language The language tag
	 * @param array $data The translations to save
	 */
	public static function saveUserTranslations($plugin, $language, $data){
		$lang = new self($plugin, $language);
		$file = $lang->getTranslatedFile();

		$lines = array();
		foreach($data as $key => $value){
			if(! is_array($value)){
				$lines[] = $key . ' = "' . addcslashes($value, '"') . '"';
			}
			else{
				foreach($value as $multiplier => $val){
					$lines[] = $key . '[' . $multiplier . '] = "' . addcslashes($val, '"') . '"';
				}
			}
		}

		$content = implode(PHP_EOL, $lines);
		$dir = dirname($file);
		if(!is_dir($dir)){
			mkdir($dir, 0755, true);
		}
		
		file_put_contents($file, $content);
		touch($file, time() + 3);
	}
}

/*** Save the language cache ***/
EventManager::on('process-end', function(Event $event){
	Lang::saveOriginCache();
});