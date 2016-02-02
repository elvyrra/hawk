<?php
/**
 * Theme.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class describes the themes behavior
 * @package Core
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
     * The pattern to find the editable variables in the less main file
     */
    const EDITABLE_VARS_PATTERN = '#^\s*@([\w\-]+)\s*\:\s*(.+?)\s*\;\s*//\s*editable\s*\:\s*"(.+?)"\s*\,?\s*(color|file)?\s*$#m';

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
    public static $nativeThemes = array('hawk', 'dark');

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
        try{
            if(!isset($themes[$name])){
                self::$themes[$name] = new self($name);
            }
            return self::$themes[$name];
        }
        catch(\Exception $e){
            return null;
        }
    }


    /**
     * Get the theme configured for the application
     * @return Theme The selected theme
     */
    public static function getSelected(){
        return self::get(App::conf()->has('db') ? Option::get('main.selected-theme') : self::DEFAULT_THEME);
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
    private function __construct($name){
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
            if(!is_file($this->getRootDir() . self::MANIFEST_BASENAME)) {
                throw new \Exception('Impossible to get the manifest.json file for the theme '  . $this->name . ' : No such file or directory');
            }
            $this->data = json_decode(file_get_contents($this->getRootDir() . self::MANIFEST_BASENAME), true);
        }
        return $prop ? $this->data[$prop] : $this->data;
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


    /**
     * Get the start file of the theme. The start file is the file start.php in the theme that initialize special intructions for the theme
     * @return string
     */
    public function getStartFile(){
        return $this->getRootDir() . 'start.php';
    }


    /**
     * Get the root directory of the theme files
     * @return string The root directory of the theme files
     */
    public function getRootDir(){
        return THEMES_DIR . $this->name . '/';
    }


    /**
     * Get the directory for HTTP accessible files. During theme build, the files are copied in this directory
     * @return string
     */
    public function getStaticDir(){
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
     * Copy a file from the theme directory to the static directory (to be accessible by HTTP requests) and returns it URL
     * @param string $file The source file to get the URL
     * @return string The public copied file URL
     */
    public function getFileUrl($file){
        $privateFile = $this->getRootDir() . $file;
        $publicFile = $this->getStaticDir() . $file;

        if(!is_file($privateFile)){
            throw new \Exception('Impossible to get the URL for the file ' . $privateFile . ' : No such file or directory');
        }

        if(!is_file($publicFile) || filemtime($publicFile) < filemtime($privateFile)){
            if(!is_dir(dirname($publicFile))){
                mkdir(dirname($publicFile), 0755, true);
            }
            App::fs()->copy($privateFile, $publicFile);
        }

        return $this->getRootUrl() . $file . '?' . filemtime($publicFile);
    }


    /**
     * Get the file path for the theme preview image
     * @return string
     */
    public function getPreviewFilename(){
        return $this->getRootDir() . self::PREVIEW_BASENAME;
    }


    /**
     * Get the URL for the theme preview image
     * @return string
     */
    public function getPreviewUrl(){
        return $this->getFileUrl(self::PREVIEW_BASENAME);
    }


    /**
     * Get the dirname containing the less files
     */
    public function getLessDirname(){
        return $this->getRootDir() . 'less/';
    }

    /**
     * Get the base CSS file path
     * @return string
     */
    public function getBaseLessFile(){
        return $this->getLessDirname() . self::LESS_BASENAME;
    }


    /**
     * Get the base less file theme.less in static folder
     * @return string
     */
    public function getStaticLessFile(){
        return $this->getStaticDir() . 'less/' . self::LESS_BASENAME;
    }

    public function getStaticCssFile(){
        return $this->getStaticDir() . 'less/' . self::COMPILED_CSS_BASENAME;
    }



    /**
     * Build the base css file of the theme, and get the URL of the built file
     * @return string The URL of the built CSS file
     */
    public function getBaseLessUrl(){
        $this->build();

        return $this->getRootUrl() . 'less/' . self::LESS_BASENAME . '?' . filemtime($this->getStaticLessFile());
    }


    public function getBaseCssUrl(){
        $this->build();

        return $this->getRootUrl() . 'less/' . self::COMPILED_CSS_BASENAME . '?' . filemtime($this->getStaticCssFile());
    }


    /**
     * Build the theme : copy every resource files in themes/{themename}
     * @param boolean $force If set to true, the theme will be rebuilt without condition
     */
    public function build($force = false){
        $build = false;
        if($force){
            $build = true;
        }

        if(!file_exists($this->getStaticDir())){
            mkdir($this->getStaticDir(), 0755, true);
            $build = true;
        }


        if(!$build){
            $dest = $this->getStaticCssFile();

            if(!is_file($dest)){
                $build = true;
            }
            else{
                // Get all files in less/
                $files = App::fs()->find($this->getLessDirname(), '*.less');
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
            foreach(glob($this->getRootDir() . '*' ) as $elt){
                if(! in_array(basename($elt), array('views', 'start.php'))) {
                    App::fs()->copy($elt, $this->getStaticDir());
                }
            }

            // In the main less file, replace the editable vars by their customized values
            $values = $this->getVariablesCustomValues();
            $precompiledLess = preg_replace_callback(self::EDITABLE_VARS_PATTERN, function($m) use($values){
                return '@' . $m[1] . ' : ' . (isset($values[$m[1]]) ? $values[$m[1]] : $m[2]) . ';';
            }, file_get_contents($this->getBaseLessFile()));

            file_put_contents($this->getStaticLessFile(), $precompiledLess);

            Less::compile($this->getStaticLessFile(), $this->getStaticCssFile());
        }

        return $build;
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
        preg_match_all(self::EDITABLE_VARS_PATTERN, $less, $matches, PREG_SET_ORDER);
        $variables = array();
        foreach($matches as $match){

            preg_match_all('#^{a-z}{/a-z}$#', $match[3], $matches_description, PREG_SET_ORDER);

            App::logger()->error('match=');
            $description = array();
            foreach($matches_description as $match_description){
                App::logger()->error('match=' . $match_description[1] . "=" . $match_description[2]);
                $description[$match_description[1]] = $match_description[2];
            }

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
     * Get the customized variables values
     * @return array The custom values
     */
    public function getVariablesCustomValues(){
        $options = Option::getPluginOptions('theme-' . $this->name);

        $values = array();
        foreach($options as $key => $value){
            if(preg_match('/^custom\-value\-(.+?)$/', $key, $m)){
                $values[$m[1]] = $value;
            }
        }

        return $values;
    }


    /**
     * Set customized variables values
     * @param array The values to set
     */
    public function setVariablesCustomValues($values){
        foreach($values as $key => $value){
            $varname = 'custom-value-' . $key;
            Option::set('theme-' . $this->name . '.' . $varname, $value);
        }
    }

    /**
     * Get the directory containing theme userfiles
     */
    public function getStaticUserfilesDir(){
        return $this->getStaticDir() . 'userfiles/';
    }


    /**
     * Get the URL of a static user file
     * @param string $filename The basename of the file to get the url
     * @return string
     */
    public function getStaticUserfilesUrl($filename = ''){
        return $this->getRootUrl() . 'userfiles/' . $filename . '?' . filemtime($this->getStaticDir() . 'userfiles/' . $filename);
    }


    /**
     * Get the directory containing the medias uplaoded by the administrator
     * @return string The directory containing the medias uploaded by the administrator
     */
    public function getMediasDir(){
        return $this->getStaticUserfilesDir() . 'medias/';
    }

    /**
     * Get the URL of the directory containing the medias uplaoded by the administrator
     * @param string $filename The basename of the file to get the URL
     * @return string The URL of the directory containing the medias uplaoded by the administrator
     */
    public function getMediasUrl($filename = ''){
        return $this->getStaticUserfilesUrl('medias/' . $filename);
    }


    /**
     * Get the file path of the CSS file customized by the application administrator
     *  @return string The file path of the custom CSS file
     */
    public function getCustomCssFile(){
        return $this->getStaticUserfilesDir() . self::CSS_CUSTOM_BASENAME;
    }


    /**
     * Get the URL of the CSS file customized by the application administrator
     * @return string The URL of the custom CSS file
     */
    public function getCustomCssUrl(){
        $file = $this->getCustomCssFile();
        if(!is_dir(dirname($file))){
            mkdir(dirname($file), 0775, true);
        }

		if(!is_file($file)){
            file_put_contents($file, '');
        }

        return $this->getStaticUserfilesUrl(self::CSS_CUSTOM_BASENAME);
    }


    /**
     * Get the directory containing the theme views
     * @return string The directory containing the theme views files
     */
    public function getViewsDir(){
        return $this->getRootDir() . 'views/';
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




}