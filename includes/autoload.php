<?php
/**
 * Autoload.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

// Autoload class needs at least FileSystem class and Plugin Class
require LIB_DIR . 'FileSystem.class.php';
require LIB_DIR . 'Plugin.class.php';


/**
 * This class describes the autoload behavior of the application
 */
class Autoload{
    /**
     * Array containing the autoload cache
     */
    private static $cache = array();
	
	// Autoload cache file
    const CACHE_FILE = CACHE_DIR . 'autoload-cache.php';

    /**
     * Variable that indicates if the cache changed
     */
    private static $cacheUpdated = false;


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

        $parts = explode('\\', $classname);
        if(count($parts) > 1){
            $namespace = implode('\\', array_slice($parts, 0, -1));
        }
        else{
            $namespace = '';
        }
        $class = end($parts);

        $filename = "$class.class.php";

		// The file is not registered in cache, let's find it. Any class file must be as <classname>.class.php
        $dirs = array();
        $searchDirectories = array(
            'Hawk' => array(LIB_DIR, CUSTOM_LIB_DIR),
            'Hawk\View\Plugins' => array(LIB_DIR . 'view-plugins/'),
            '' => array(LIB_DIR . 'ext', CUSTOM_LIB_DIR)
        );

        if(isset($searchDirectories[$namespace])){
            $dirs = $searchDirectories[$namespace];
        }
        elseif(strpos($namespace, 'Hawk\\Plugins\\') === 0){
            if(class_exists("\\Hawk\\$class") || trait_exists("\\Hawk\\$class")){
                class_alias("\\Hawk\\$class", $classname);
                return true;
            }
            else{
                // Find the plugins associated to this namespace
                $plugins = Plugin::getAll();
                foreach($plugins as $plugin){
                    if($plugin->getNamespace() === $namespace){
                        $dirs = array($plugin->getRootDir());
                        break;
                    }
                }                
            }
        }
        else{
            // If the class exists, it is in custom-libs directory
            $dirs = array(CUSTOM_LIB_DIR, LIB_DIR . 'ext/');
        }

		// Cross any search folder to find out the class file
        foreach($dirs as $dir){
            $files = FileSystem::find($dir, $filename, FileSystem::FIND_FILE_ONLY);
            if(!empty($files)){
                $file = $files[0];

                // The class file has been found, include it
                include $file;

                // Register this file, associated to the class name, in cache
                self::$cache[$classname] = $file;
                self::$cacheUpdated = true;

                return true;
            }
        }  
    }


    /**
	 * Save the autoload cache at the end of a script processing. It is not registered any times it is updated,
	 * to improve the performances of the application.
	 */
    public static function saveCache(){        
        if(self::$cacheUpdated){
            file_put_contents(self::CACHE_FILE, "<?php return ". var_export(self::$cache, true) . ";");
        }
    }
}

// register autoload function 
spl_autoload_register('\Hawk\Autoload::load', true, false);

// Save the autoload cache 
Event::on('process-end', function(Event $event){     
    Autoload::saveCache();
});