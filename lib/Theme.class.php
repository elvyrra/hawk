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
    
    public function getBuildDirname(){
        return USERFILES_THEMES_DIR . $this->name . '/';
    }
    
    public function getRootUrl(){
        return USERFILES_THEMES_URL . $this->name . '/';
    }
    
    public function getBaseCssFile(){
        return $this->getRootDirname() . self::CSS_BASENAME;
    }
    
    public function getBuildCssFile(){
        return $this->getBuildDirname() . self::CSS_BASENAME;
    }
    
    public function buildCssFile($force = false){
        if(!file_exists($this->getBuildDirname()))
            mkdir($this->getBuildDirname());
        
        if($force || !is_file($this->getBuildCssFile()) || filemtime($this->getBaseCssFile()) > filemtime($this->getBuildCssFile())){
            // Build the css
			$css = file_get_contents($this->getBaseCssFile());
            
			// Get the theme options
			$options = Option::getPluginOptions('theme-' . $this->name);
			
			// Replace the variables
            preg_match_all('#^/\*\s+define\s*\(\s*(\w+)\s*,\s*(color|dimension|file)\s*,\s*"(.+?)"\s*,\s*(.+?)\)\s*\*/#m', $css, $matches, PREG_SET_ORDER);            			
            foreach($matches as $match){
                $var = $match[1];
                $type = $match[2];
                $value = $match[4];
				
				// Get the configured value in the options table
				if(isset($options['value-'.$var])){
					$value = $options['value-'.$var];
				}
                				
                $css = str_replace("@$var", $value, $css);
            }
			
			if(!(DEV_MODE || DEBUG_MODE)){
				// Minify the css result
				$css = preg_replace(array(
                    '!/\*(.*?)\*/!m', // remove comments
                    '!\s+([\:\{\};,])!', // remove whitespaces before colons, semi-colons, and parenthesis                    
                    '!([\:\{\};,])\s+!', // remove whitespaces after colons, semi-colons, and parenthesis
                    '!^\s+!', // remove whitespaces starting line
                    '![\t\r\n]+!', // remove line returns and tabs
                ),
                array(
                    '',
                    '$1',
                    '$1',                    
                    '',
                    ''
                ),
				$css);
			}
            
            file_put_contents($this->getBuildCssFile(), $css);
        }
    }
    
    public function getBaseCssUrl(){
        $this->buildCssFile(DEV_MODE || NO_CACHE);
        return $this->getRootUrl() . self::CSS_BASENAME;
    }
    
    public function getCustomCssFile(){
        return $this->getRootDirname() . self::CSS_CUSTOM_BASENAME;
    }
    
    public function getCustomCssUrl(){
		if(is_file($this->getCustomCssFile())){
			return $this->getRootUrl() . self::CSS_CUSTOM_BASENAME;
		}
		else{
			return '';
		}
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