<?php
/**
 * Theme.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

/**
 * This class describes the themes behavior
 * @package Core\Theme
 */
class Theme{ 
    /**
     * The theme name
     * @var string
     */
    private $name, 

    /**
     * The theme manifest data
     * @var array
     */
    $data,

    /**
     * The parent theme name
     * @var Theme
     */
    $parent = null,

    /**
     * The CSS sources, inherited from parent theme
     */
    $sources = array();

    
    /**
     * The theme css file basename
     */
    const LESS_BASENAME = 'theme.less';

    /**
     * The filename of the compiled Css file
     */
    const COMPILED_CSS_BASENAME = 'theme.css';

    /**
     * The theme css custom file basename
     */
    const CSS_CUSTOM_BASENAME = 'theme-custom.css';

    /**
     * The themes embedded with Hawk, that are not removable
     */
    private static $nativeThemes = array("hawk");
    
    /**
     * Constructor
     * @param string $name The theme name     
     */
    public function __construct($name){
        $this->name = $name;

        $this->getData();

        if(isset($this->data['extends'])){
            $this->parent = ThemeManager::get($this->data['extends']);
        }
        elseif($this->name != ThemeManager::DEFAULT_THEME){
            $this->parent = ThemeManager::get(ThemeManager::DEFAULT_THEME);
        }

        $this->sources = $this->getCssSources();
    }


    /**
     * Get the theme data in the file manifest.json. 
     * If $prop is set, this method returns the property value in the theme data, else it returns all the theme data
     * @param string $prop The property in data to get
     * @return mixed The value of the property $prop if it is set, else, all the theme data
     */
    public function getData($prop = ""){    
        if(!isset($this->data)){            
            $this->data = json_decode(file_get_contents($this->getRootDirname() . 'manifest.json'), true);
        }
        return $prop ? $this->data[$prop] : $this->data;
    }
    

    /**
     * Get the root directory of the theme files
     * @return string The root directory of the theme files
     */
    public function getRootDirname(){
        return THEMES_DIR . $this->name . '/';    
    }
    

    /**
     * Get the directory where theme is built during script execution
     * @return string
     */
    public function getBuildDirname(){
        return USERFILES_THEMES_DIR . $this->name . '/';
    }
    

    /**
     * Get the root URL to get theme files by HTTP request
     * @return string
     */
    public function getRootUrl(){
        return USERFILES_THEMES_URL . $this->name . '/';
    }


    /**
     * Get the file path for the theme preview image
     * @return string
     */
    public function getPreviewFilename(){
        return $this->getRootDirname() . 'preview.png';        
    }


    /**
     * Get the URL for the theme preview image
     * @return string
     */
    public function getPreviewUrl(){
        return THEMES_ROOT_URL . $this->name . '/preview.png';
    }
    

    /**
     * Get the base CSS file path
     * @return string
     */
    public function getBaseCssFile(){
        $file = $this->getRootDirname() . self::LESS_BASENAME;
        return $file;
    }


    /**
     * Get the base CSS content (concatenate recursively parent css contents)
     * @return string The compiler Css content
     */
    public function getBaseCssContent(){
        return implode(PHP_EOL, array_map(function($file){
            if(is_file($file)){
                return file_get_contents($file);
            }
            else{
                return '';
            }
        }, $this->sources));
    }


    /**
     * Get the base CSS sources, getting all the parents CSS filenames
     * @return array An array containing all the sources used to build this theme CSS file
     */
    public function getCssSources(){
        $sources = array();
        if($this->parent){
            $sources = array_merge($sources, $this->parent->getCssSources());
        }
        $sources[] = $this->getBaseCssFile();        

        return $sources;
    }
    

    /**
     * Get the base CSS file URL, after been built
     * @return string
     */
    public function getBuildCssFile(){
        return $this->getBuildDirname() . self::COMPILED_CSS_BASENAME;
    }
    

    /**
     * Build the base CSS file into build directory (replace variables by default or configured values)
     * @param boolean $force If set to true, the built CSS file will be overriden, whereas it has not been modified since the last build. Else, if the file has not been modified since the last build, nothing will be done
     */
    public function buildCssFile($force = false){
        if(!file_exists($this->getBuildDirname())){
            mkdir($this->getBuildDirname(), 0755, true);
        }

        $build = false;
        if($force || !is_file($this->getBuildCssFile())){
            $build = true;
        }
        else{
            foreach($this->sources as $source){
                if(filemtime($source) > filemtime($this->getBuildCssFile())){
                    $build = true;
                    Utils::debug('file updated');
                    break;
                }
            }
        }

        if($build){              
            // Build the css
			$css = $this->getBaseCssContent();
            
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
                $css = preg_replace('/@' . $varname . '\s*\:\s*(' . $variable['default'] . ')/', '@' . $varname . ': ' . $value, $css);
            }

            $less = new lessc;
			if(!(DEV_MODE || DEBUG_MODE)){
                $less->setFormatter("compressed");
                $less->setPreserveComments(true);
            }
            $compiled = $less->compile($css);
				
            FileSystem::copy($this->getRootDirname(), USERFILES_THEMES_DIR);

            file_put_contents($this->getBuildCssFile(), $compiled);
        }
    }


    /**
     * Get the variables in a CSS file content. In CSS files, variables are defined with the folowing format :
     * /* define("variableName", color|dimension|file, "variable description that will appear in the customization page of the theme", defaultValue) *\/
     * @param string $css The CSS code to parse
     * @return array The variables, where each element contains the 'name' of the variabme, it 'type', it 'description', and it 'default' value
     */
    public function getCssVariables(){
        $css = $this->getBaseCssContent();
        preg_match_all('#^\s*@([\w\-]+)\s*\:\s*(.*?)\s*\;\s*//\s*"(.+?)"\s*,\s*(color|dimension|file)#m', $css, $matches, PREG_SET_ORDER);                     
        $variables = array();
        foreach($matches as $match){
            $variables[] = array(
                'name' => $match[1],
                'default' => $match[2],
                'description' => $match[3],
                'type' => $match[4]
            );
        }
        return $variables;
    }
    

    /**
     * Build the base css file of the theme, and get the URL of the built file
     * @return string The URL of the built CSS file
     */
    public function getBaseCssUrl(){
        $this->buildCssFile();
        return $this->getRootUrl() . self::COMPILED_CSS_BASENAME;
    }
    
    /**
     * Get the file path of the CSS file customized by the application administrator
     *  @return string The file path of the custom CSS file
     */
    public function getCustomCssFile(){
        return $this->getBuildDirname() . self::CSS_CUSTOM_BASENAME;
    }
    

    /**
     * Get the URL of the CSS file customized by the application administrator
     * @return string The URL of the custom CSS file
     */
    public function getCustomCssUrl(){
		if(!is_file($this->getCustomCssFile())){
            file_put_contents($this->getCustomCssFile(), '');
        }
		
        return $this->getRootUrl() . self::CSS_CUSTOM_BASENAME;		
    }
    

    /**
     * Get the directory of the theme images, used for CSS and / or views
     * @return string The directory contaning the theme images
     */
    public function getImagesDir(){
        return $this->getRootDirname() . 'images/';
    }
    

    /**
     * Get the URL of the directory of the theme images
     * @retur string The URL of the directory containing the theme images
     */
    public function getImagesRootUrl(){
        return $this->getRootUrl() . 'images/';
    }
    
    /**
     * Get the directory containing the theme views
     * @return string The directory containing the theme views files
     */
    public function getViewsDir(){
        return $this->getRootDirname() . 'views/';
    }
    

    /**
     * Get the filename of a view in the theme. If the view file does not exists in the theme, the method will return the view in the default theme
     * @param string $filename The basename of the view file, relative to the theme views directory path
     * @return string The path of the view file
     */
    public function getView($filename){
        $file = $this->getViewsDir() . $filename;
        if(!is_file($file) && $this->name != ThemeManager::DEFAULT_THEME){
            if($this->parent){
                // The view does not exists in the theme, and the theme is not the default one, Try to get the view file in the default theme
                $file = $this->parent->getView($filename);
            }
            else{
                $file = ThemeManager::get(ThemeManager::DEFAULT_THEME)->getView($filename);
            }
        }
        return $file;
    }

    /**
     * Get the directory containing the medias uplaoded by the administrator
     * @return string The directory containing the medias uploaded by the administrator
     */
    public function getMediasDir(){
        return $this->getBuildDirname() . 'medias/';        
    }

    /**
     * Get the URL of the directory containing the medias uplaoded by the administrator
     * @return string The URL of the directory containing the medias uplaoded by the administrator
     */
    public function getMediasUrl(){
        return $this->getRootUrl() . 'medias/';
    }


    /**
     * Get the theme title (data accessible in the manifest.json file of the theme)
     * @return string the theme title
     */
    public function getTitle(){
        return $this->getData('title');
    }

    /**
     * Get the theme name (the name of the directory containing the theme)
     * @return string The theme name
     */
    public function getName(){
        return $this->name;
    }

    /**
     * Check if the theme is removable. A theme is removable if it's not a native theme, and if it is not the selected one for the application
     * @return boolean true if the theme is removable, else false.
     */
    public function isRemovable(){
        return !in_array($this->name, self::$nativeThemes) && ThemeManager::getSelected() != $this;
    }
}