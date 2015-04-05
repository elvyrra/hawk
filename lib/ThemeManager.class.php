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
}