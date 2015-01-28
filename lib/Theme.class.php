<?php

class Theme{ 
    private static $instance;
    private $name, $data;
    
    const CSS_BASENAME = 'theme.css';
    const CSS_CUSTOM_BASENAME = 'theme-custom.css';
    
    
    public function __construct($name){
        $this->name = $name;
    }
    
    public function getRootDirname(){
        return THEMES_DIR . $this->name . '/';    
    }
    
    public function getRootUrl(){
        return THEMES_ROOT_URL . $this->name . '/';
    }
    
    public function getBaseCssFile(){
        return $this->getRootDirname() . self::CSS_BASENAME;
    }
    
    public function getBaseCssUrl(){
        return $this->getRootUrl() . self::CSS_BASENAME;
    }
    
    public function getCustomCssFile(){
        return $this->getRootDirname() . self::CSS_CUSTOM_BASENAME;
    }
    
    public function getCustomCssUrl(){
        return $this->getRootUrl() . self::CSS_CUSTOM_BASENAME;
    }
    
    public function getImagesDir(){
        return $this->getRootDirname() . 'images/';
    }
    
    public function getImagesRootUrl(){
        return $this->getRootUrl() . 'images/';
    }
    
    public function getViewsDir(){
        return $this->getRootDirname() . 'views/';
    }
    
    public function getView($filename){
        $file = $this->getViewsDir() . $filename;
        if(!is_file($file) && $this->name != ThemeManager::DEFAULT_THEME){
            $file = ThemeManager::get(ThemeManager::DEFAULT_THEME)->getView($filename);
        }
        
        return $file;
    }
}