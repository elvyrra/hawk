<?php

class ThemeManager{
    private static $instances;
    const DEFAULT_THEME = 'mint';
    
    public static function get($name = ''){
        if(empty($name))
            $name = self::DEFAULT_THEME;
            
        if(!isset($instances[$name])){
            self::$instances[$name] = new Theme($name);
        }
        return self::$instances[$name];
    }
    
    public function getSelected(){
        return self::get(Option::get('main.selectedTheme'));
    }
    
    public static function setSelected($name){
        Options::set('main.selectedTheme', $name);        
    }
    
    public function getView($template){
        $file = self::getSelected()->getView($template);
        if(!is_file($file))
            $file = self::get(self::DEFAULT_THEME)->getView($template);
        return $file;
    }
}