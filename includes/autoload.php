<?php
class Autoload{
    private static $cache = array();
    private static $cacheFile;
    
    public static function load($classname){
        self::$cacheFile = CACHE_DIR . 'autoload-cache.php';
        
        if(is_file(self::$cacheFile) && empty(self::$cache)){
            self::$cache = include self::$cacheFile;            
        }
        if(isset(self::$cache[$classname])){
            include self::$cache[$classname];
            return true;
        }
        $dirs = array(PLUGINS_DIR, MAIN_PLUGINS_DIR, LIB_DIR);
        $extensions = array(
            'Controller' => '.ctrl.php',
            'Widget' => '.widget.php',
            'Model' => '.model.php',        
        );
        
        if(preg_match('/^(\w+)(' . implode('|', array_keys($extensions)) . ')$/', $classname, $match)){
            $ext = $extensions[$match[2]];
            $base = $match[1];
        }
        else{
            $ext = '.class.php';
            $base = $classname;
            $dirs = array_reverse($dirs);
        }
        
        foreach($dirs as $dir){
            exec("find $dir -type f -name '{$base}{$ext}'", $files, $return);
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