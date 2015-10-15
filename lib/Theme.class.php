<?php
/**
 * Theme.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class describes the themes behavior
 * @package Core\Theme
 */
class Theme{ 
    /**
     * The default theme name
     */
    const DEFAULT_THEME = 'hawk';

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
     * The filename of the definition file
     */
    const MANIFEST_BASENAME = 'manifest.json';

    /**
     * The filename of the preview image
     */
    const PREVIEW_BASENAME = 'preview.png';

    /**
     * The pattern for a theme name     
     */
    const NAME_PATTERN = '[a-zA-Z0-9\-_.]+';

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
    $parent = null;

    /**
     * The themes embedded with Hawk, that are not removable
     */
    private static $nativeThemes = array('hawk');

    /**
     * The instanciated themes
     */
    private static $themes;


    /**
     * Instanciate a new theme by it name
     * @param string $name The name of the theme, corresponding to it directory name under /themes
     * @return Theme The found theme
     */
    public static function get($name = self::DEFAULT_THEME){
        if(!isset($themes[$name])){
            self::$themes[$name] = new self($name);
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
        foreach(glob(THEMES_DIR . '*', GLOB_ONLYDIR) as $theme){
            self::get(basename($theme));
        }

        return self::$themes;
    } 

    /**
     * Constructor
     * @param string $name The theme name     
     */
    public function __construct($name){
        $this->name = $name;

        $this->getDefinition();

        if(isset($this->data['extends'])){
            $this->parent = self::get($this->data['extends']);
        }
        elseif($this->name != self::DEFAULT_THEME){
            $this->parent = self::get(self::DEFAULT_THEME);
        }
    }


    /**
     * Get the theme data in the file manifest.json. 
     * If $prop is set, this method returns the property value in the theme data, else it returns all the theme data
     * @param string $prop The property in data to get
     * @return mixed The value of the property $prop if it is set, else, all the theme data
     */
    public function getDefinition($prop = ""){    
        if(!isset($this->data)){ 
            if(!is_file($this->getRootDirname() . self::MANIFEST_BASENAME)) {
                throw new \Exception('Impossible to get the manifest.json file for the theme '  . $this->name . ' : No such file or directory');
            }
            $this->data = json_decode(file_get_contents($this->getRootDirname() . self::MANIFEST_BASENAME), true);
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
        return STATIC_THEMES_DIR . $this->name . '/';
    }
    

    /**
     * Get the root URL to get theme files by HTTP request
     * @return string
     */
    public function getRootUrl(){
        return THEMES_ROOT_URL . $this->name . '/';
    }


    /**
     * Get the file path for the theme preview image
     * @return string
     */
    public function getPreviewFilename(){
        return $this->getRootDirname() . self::PREVIEW_BASENAME;
    }


    /**
     * Get the URL for the theme preview image
     * @return string
     */
    public function getPreviewUrl(){
        $privateFilename = $this->getPreviewFilename();
        $publicFilename = $this->getBuildDirname() . self::PREVIEW_BASENAME;

        if(is_file($privateFilename) && (!is_file($publicFilename) || filemtime($privateFilename) > filemtime($publicFilename))){
            if(!is_dir(dirname($publicFilename))){
                mkdir(dirname($publicFilename), 0755, true);
            }
            copy($privateFilename, $publicFilename);
        }
        return $this->getRootUrl() . self::PREVIEW_BASENAME;
    }
    

    /**
     * Get the dirname containing the less files
     */
    public function getLessDirname(){
        return $this->getRootDirname() . 'less/';
    }

    /**
     * Get the base CSS file path
     * @return string
     */
    public function getBaseLessFile(){
        return $this->getLessDirname() . self::LESS_BASENAME;        
    }


    /**
     * Get the base LESS file URL, after been built
     * @return string
     */
    public function getBuildLessFile(){
        return $this->getBuildDirname() . 'less/' . self::LESS_BASENAME;
    }

   
    /**
     * Get the base CSS file URL, after been built
     * @return string
     */
    public function getBuildCssFile(){
        return $this->getBuildDirname() . self::COMPILED_CSS_BASENAME;
    }
    

    /**
     * Get the path of the file contaning the data of the last theme compilation
     * @return string the path of the file
     */
    private function getLastCompilationInfoFilename(){
        return CACHE_DIR . 'theme-' . $this->name . '-compilation-info.php';
    }

    /**
     * Build the theme : build the Less file theme.less into theme.css and copy every resource files in userfiles/themes/{themename}
     * @param boolean $force If set to true, the theme will be rebuilt without condition
     */
    public function build($force = false){        
        $build = false;
        if($force){
            $build = true;
        }

        if(!file_exists($this->getBuildDirname())){
            mkdir($this->getBuildDirname(), 0755, true);
            $build = true;
        }

        
        if(!$build){
            $dest = $this->getBuildLessFile();

            if(!is_file($dest)){
                $build = true;
            }          
            else{
                // Get all files in less/
                $files = FileSystem::find($this->getLessDirname(), '*.less');
                $lastUpdate = filemtime($dest);
                foreach($files as $file){
                    if(filemtime($file) > $lastUpdate){
                        $build = true;
                        break;
                    }
                }
            }
        }

        if($build){
            // Build the theme => Copy each accessible files in static dir
            foreach(glob($this->getRootDirname() . '*', GLOB_ONLYDIR ) as $elt){
                if(! in_array(basename($elt), array('views'))) {
                    FileSystem::copy($elt, $this->getBuildDirname());
                }
            }
        }

        return $build;
    // }

        // // Listen for compilation success
        // Event::on('built-less', function(Event $event){
        //     if($event->getData('source') === $this->getBaseLessFile()){
        //         foreach(glob($this->getRootDirname() . '*', GLOB_ONLYDIR ) as $elt){
        //             if(! in_array(basename($elt), array('less', 'views'))) {
        //                 FileSystem::copy($elt, $this->getBuildDirname());
        //             }
        //         }
        //     }
        // });

        // // Get the theme options
        // $editableVariables = $this->getEditableVariables();        
        // if(Conf::has('db')){
        //     $options = Option::getPluginOptions('theme-' . $this->name);
        // }
        // else{
        //     $options = array();
        // }

        // $variables = array();
        // foreach($editableVariables as $variable){
        //     $variables[$variable['name']] = isset($options[$variable['name']]) ? $options[$variable['name']] : $variable['default'];
        // }


        // Less::compile($this->getBaseLessFile(), $this->getBuildCssFile(), $force, $variables);       
        
    }


    /**
     * Get the variables in a CSS file content. In CSS files, variables are defined with the folowing format :
     * /* define("variableName", color|dimension|file, "variable description that will appear in the customization page of the theme", defaultValue) *\/
     * @param string $less The Less code to parse
     * @return array The variables, where each element contains the 'name' of the variable, it 'type', it 'description', and it 'default' value
     */
    public function getEditableVariables($less = null){
        if(!$less){
            $less = file_get_contents($this->getBaseLessFile());
        }
        preg_match_all('#^\s*define\(@([\w\-]+)\s*\;\s*(.+?)\s*\;\s*(.+?)\s*\;?\s*(color|file)?\s*\)\s*$#m', $less, $matches, PREG_SET_ORDER);                     
        $variables = array();
        foreach($matches as $match){
            $variables[] = array(
                'name' => $match[1],
                'default' => $match[2],
                'description' => $match[3],
                'type' => isset($match[4]) ? $match[4] : ''
            );
        }
        return $variables;
    }
    

    /**
     * Build the base css file of the theme, and get the URL of the built file
     * @return string The URL of the built CSS file
     */
    public function getBaseLessUrl(){
        $this->build();
        return $this->getRootUrl() . 'less/' . self::LESS_BASENAME;
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
        if(!is_file($file) && $this->name != self::DEFAULT_THEME){
            if($this->parent){
                // The view does not exists in the theme, and the theme is not the default one, Try to get the view file in the default theme
                $file = $this->parent->getView($filename);
            }
            else{
                $file = self::get(self::DEFAULT_THEME)->getView($filename);
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
        return $this->getDefinition('title');
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
        return !in_array($this->name, self::$nativeThemes) && self::getSelected() != $this;
    }
}