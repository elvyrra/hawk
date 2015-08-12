<?php
/**
 * ThemeManager.class.php
 */



/**
 * This class manage the installed themes
 * @package Core\Theme
 */
class ThemeManager{
    const DEFAULT_THEME = 'hawk';

    /**
     * The instanciated themes
     */
    private static $themes;
    
    /**
     * Instanciate a new theme by it name
     * @param string $name The name of the theme, corresponding to it directory name under /themes
     * @return Theme The found theme
     */
    public static function get($name = ''){
        if(empty($name)){
            $name = self::DEFAULT_THEME;
        }
            
        if(!isset($themes[$name])){
            self::$themes[$name] = new Theme($name);
        }
        return self::$themes[$name];
    }
    

    /**
     * Get the theme configured for the application
     * @return Theme The selected theme
     */
    public static function getSelected(){
        return self::get(Conf::has('db') ? Option::get('main.selected-theme') : self::DEFAULT_THEME);
    }
    
    /**
     * Set a theme as the selected one for the application
     * @param string $name The name of the theme
     */
    public static function setSelected($name){
        Option::set('main.selected-theme', $name);        
    }  

    /**
     * Get all the available themes
     * @return array All the themes
     */
    public static function getAll(){        
        foreach(glob(THEMES_DIR . '*') as $theme){
            self::get(basename($theme));
        }

        return self::$themes;
    } 
}