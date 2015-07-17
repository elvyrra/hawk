<?php

class ThemeManager{
    private static $themes;
    const DEFAULT_THEME = 'hawk';
    
    public static function get($name = ''){
        if(empty($name)){
            $name = self::DEFAULT_THEME;
        }
            
        if(!isset($themes[$name])){
            self::$themes[$name] = new Theme($name);
        }
        return self::$themes[$name];
    }
    
    public static function getSelected(){
        return self::get(Conf::has('db') ? Option::get('main.selected-theme') : self::DEFAULT_THEME);
    }
    
    public static function setSelected($name){
        Option::set('main.selected-theme', $name);        
    }  

    public static function getAll(){        
        foreach(glob(THEMES_DIR . '*') as $theme){
            self::get(basename($theme));
        }

        return self::$themes;
    } 
}