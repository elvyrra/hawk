<?php
/**
 * This class describes the autoload behavior of the application
 * @author Elvyrra SAS
 */
class Autoload{
	// Array containing the autoload cache
    private static $cache = array();
	
	// Autoload cache file
    const CACHE_FILE = CACHE_DIR . 'autoload-cache.php';

	// folders where to search classes declarations
    private static $searchDirectories = array(
        LIB_DIR,
        PLUGINS_DIR, 
        MAIN_PLUGINS_DIR,
        CUSTOM_LIB_DIR
    );
	
	/**
	 * Load a file containing the wanted class
	 * @param string $classname The class to load
	 */
	public static function load($classname){
        // Load the cache file for the first time the autload is called
		if(is_file(self::CACHE_FILE) && empty(self::$cache)){
            self::$cache = include self::CACHE_FILE;            
        }
		
		// Check the class file is registered in cache
		if(isset(self::$cache[$classname])){
			// The file is registered in cache, include it, and exit the function
            include self::$cache[$classname];
            return true;
        }

		// The file is not registered in cache, let's find it. Any class file must be as <classname>.class.php
        $filename = "$classname.class.php";
		
		// Cross any search folder to find out the class file
        foreach(self::$searchDirectories as $dir){			
            exec("find $dir -type f -name '$filename'", $files, $return);
            if(empty($return) && !empty($files)){
				// The class file has been found, include it
                include $files[0];
				
				// Register this file, associated to the class name, in cache
                self::$cache[$classname] = $files[0];
				
                return true;
            }
        }
    }
        
	/**
	 * Save the autoload cache at the end of a script processing. It is not registered any times it is updated,
	 * to improve the performances of the application.
	 */
    public static function saveCache(){
        file_put_contents(self::CACHE_FILE, "<?php return ". var_export(self::$cache, true) . ";");        
    }
}

spl_autoload_register('Autoload::load', true, false);

/*** Save the autoload cache ***/
EventManager::on('process-end', function(Event $event){
	Autoload::saveCache();
});