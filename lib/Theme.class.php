<?php


class Theme{ 
    private static $instance;
    private $name, $data;
    
    const CSS_BASENAME = 'theme.css';
    const CSS_CUSTOM_BASENAME = 'theme-custom.css';

    private static $nativeThemes = array("hawk", "hawk-dark");
    
    
    public function __construct($name){
        $this->name = $name;

        $this->getData();
    }

    public function getData($prop = ""){    
        if(!isset($this->data)){            
            $this->data = json_decode(file_get_contents($this->getRootDirname() . 'manifest.json'), true);
        }
        return $prop ? $this->data[$prop] : $this->data;
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

    public function getPreviewFilename(){
        return $this->getRootDirname() . 'preview.png';        
    }

    public function getPreviewUrl(){
        return THEMES_ROOT_URL . $this->name . '/preview.png';
    }
    
    public function getBaseCssFile(){
        return $this->getRootDirname() . self::CSS_BASENAME;
    }
    
    public function getBuildCssFile(){
        return $this->getBuildDirname() . self::CSS_BASENAME;
    }
    
    public function buildCssFile($force = false){
        if(!file_exists($this->getBuildDirname())){
            mkdir($this->getBuildDirname(), 0755, true);
        }
        
        if($force || !is_file($this->getBuildCssFile()) || filemtime($this->getBaseCssFile()) > filemtime($this->getBuildCssFile())){
            // Build the css
			$css = file_get_contents($this->getBaseCssFile());
            
			// Get the theme options
            if(Conf::has('db')){
                $options = Option::getPluginOptions('theme-' . $this->name);
            }
            else{
                $options = array();
            }


			// Replace the variables
            $variables = $this->getCssVariables($css);
            foreach($variables as $variable){
                $varname = $variable['name'];
                $value = $variable['default'];
				
				// Get the configured value in the options table
				if(isset($options[$varname])){
					$value = $options[$varname];
				}
                $css = str_replace("@$varname", $value, $css);
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
            
            shell_exec("cp -r {$this->getRootDirname()}/* {$this->getBuildDirname()}");

            file_put_contents($this->getBuildCssFile(), $css);
        }
    }

    public function getCssVariables($css){
        preg_match_all('#^/\*\s+define\s*\(\s*(\w+)\s*,\s*(color|dimension|file)\s*,\s*"(.+?)"\s*,\s*(.+?)\)\s*\*/#m', $css, $matches, PREG_SET_ORDER);                     
        $variables = array();
        foreach($matches as $match){
            $variables[] = array(
                'name' => $match[1],
                'type' => $match[2],
                'description' => $match[3],
                'default' => $match[4]
            );
        }
        return $variables;
    }
    
    public function getBaseCssUrl(){
        $this->buildCssFile(DEV_MODE || NO_CACHE);
        return $this->getRootUrl() . self::CSS_BASENAME;
    }
    
    public function getCustomCssFile(){
        return $this->getBuildDirname() . self::CSS_CUSTOM_BASENAME;
    }
    
    public function getCustomCssUrl(){
		if(!is_file($this->getCustomCssFile())){
            file_put_contents($this->getCustomCssFile(), '');
        }
		
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

    public function getMediasDir(){
        return $this->getBuildDirname() . 'medias/';        
    }

    public function getMediasUrl(){
        return $this->getRootUrl() . 'medias/';
    }

    public function getTitle(){
        return $this->getData('title');
    }

    public function getName(){
        return $this->name;
    }

    public function isRemovable(){
        return !in_array($this->name, self::$nativeThemes) && ThemeManager::getSelected() != $this;
    }
}