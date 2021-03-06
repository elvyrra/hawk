<?php
/**
 * Autoload.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

// Autoload class needs at least FileSystem class and Plugin Class
require LIB_DIR . 'Singleton.php';
require LIB_DIR . 'FileSystem.php';
require LIB_DIR . 'Plugin.php';
require LIB_DIR . 'Cache.php';


/**
 * This class describes the autoload behavior of the application
 *
 * @package Core
 */
class Autoload{
    /**
     * Array containing the autoload cache
     */
    private static $cache = array();

    // Autoload cache file
    const CACHE_FILE = 'autoload-cache.php';

    /**
     * Variable that indicates if the cache changed
     */
    private static $cacheUpdated = false;


    /**
     * Load a file containing the wanted class
     *
     * @param string $classname The class to load
     */
    public static function load($classname){
        // Load the cache file for the first time the autload is called
        if(empty(self::$cache) && is_file(Cache::getInstance()->getCacheFilePath(self::CACHE_FILE))) {
            self::$cache = Cache::getInstance()->includeCache(self::CACHE_FILE);

            if(!is_array(self::$cache)) {
                // The format of the cache is incorrect
                self::$cache = array();
            }
        }

        // Check the class file is registered in cache
        if(isset(self::$cache[$classname])) {
            // The file is registered in cache, include it, and exit the function
            include self::$cache[$classname];
            return true;
        }

        $parts = explode('\\', $classname);
        if(count($parts) > 1) {
            $namespace = implode('\\', array_slice($parts, 0, -1));
        }
        else{
            $namespace = '';
        }
        $class = end($parts);

        $filename = "$class.php";

        // The file is not registered in cache, let's find it. Any class file must be as <classname>.php
        $dirs = array();
        $searchDirectories = array(
            'Hawk' => array(LIB_DIR, CUSTOM_LIB_DIR),
            'Hawk\View\Plugins' => array(LIB_DIR . 'view-plugins/'),
            'Hawk\Middlewares' => array(LIB_DIR . 'middlewares/'),
            '' => array(LIB_DIR . 'ext', CUSTOM_LIB_DIR)
        );

        if(isset($searchDirectories[$namespace])) {
            $dirs = $searchDirectories[$namespace];
        }
        elseif(strpos($namespace, 'Hawk\\Plugins\\') === 0) {
            // Find the plugins associated to this namespace
            $plugins = Plugin::getAll();
            foreach($plugins as $plugin){
                if($plugin->getNamespace() === $namespace) {
                    $dirs = array($plugin->getRootDir());
                    break;
                }
            }
        }
        else{
            // If the class exists, it is in custom-libs directory
            $dirs = array(CUSTOM_LIB_DIR, LIB_DIR . 'ext/');
        }

        // Cross any search folder to find out the class file
        foreach($dirs as $dir){
            $files = FileSystem::getInstance()->find($dir, $filename, FileSystem::FIND_FILE_ONLY);
            if(!empty($files)) {
                $file = $files[0];

                // The class file has been found, include it
                include $file;

                // Register this file, associated to the class name, in cache
                self::$cache[$classname] = $file;
                self::$cacheUpdated = true;

                return true;
            }
        }

        if(strpos($namespace, 'Hawk\\Plugins\\') === 0) {
            // If the class is an hawk class called from a plugin ,
            // create an alias from the Hawk class to the plugin namespace
            $alias = '\\Hawk\\' . $class;
            if(class_exists($alias) || trait_exists($alias)) {
                class_alias($alias, $classname);

                return true;
            }
        }
    }


    /**
     * Save the autoload cache at the end of a script processing. It is not registered any times it is updated,
     * to improve the performances of the application.
     */
    public static function saveCache(){
        if(self::$cacheUpdated) {
            Cache::getInstance()->save(self::CACHE_FILE, '<?php return '. var_export(self::$cache, true) . ';');
        }
    }
}

// register autoload function
spl_autoload_register('\Hawk\Autoload::load', true, false);

// Save the autoload cache
Event::on(
    'process-end', function (Event $event) {
        Autoload::saveCache();
    }
);
