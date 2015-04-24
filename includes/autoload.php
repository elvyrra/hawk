<?php
class Autoload{
    private static $cache = array();
    private static $cacheFile;

    private static $searchDirectories = array(
        LIB_DIR,
        PLUGINS_DIR, 
        MAIN_PLUGINS_DIR,
        CUSTOM_LIB_DIR
    );
	
	public static function load($classname){
		self::$cacheFile = CACHE_DIR . 'autoload-cache.php';		
        
        if(is_file(self::$cacheFile) && empty(self::$cache)){
            self::$cache = include self::$cacheFile;            
        }
        if(isset(self::$cache[$classname])){
            include self::$cache[$classname];
            return true;
        }

        $filename = "$classname.class.php";
        foreach(self::$searchDirectories as $dir){
            exec("find $dir -type f -name '$filename'", $files, $return);
            if(empty($return) && !empty($files)){                
                include $files[0];
                self::$cache[$classname] = $files[0];
                return true;
            }
        }
    }
        
    public static function saveCache(){
        file_put_contents(self::$cacheFile, "<?php return ". var_export(self::$cache, true) . ";");        
    }
}

spl_autoload_register('Autoload::load', true, false);