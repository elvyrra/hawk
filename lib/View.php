<?php
/**
 * View.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the behavior of views
 *
 * @package View
 */
class View{
    /**
     * The source file of the view
     */
    private $file,

    /**
     * The content of the source template
     *
     * @var string
     */
    $content,

    /**
     * The data injected in the view for generation
     *
     * @var array
     */
    $data,

    /**
     * Defines if the view already cached or not
     *
     * @var boolean
     */
    $cached = false;

    /**
     * The instanced views
     */
    private static $instances = array();

    /**
     * The directory containing the views plugins
     */
    const PLUGINS_VIEW = 'view-plugins/';

    /**
     * The regular expression to match control structure openings
     */
    const BLOCK_START_REGEX = '#\{(if|elseif|else|for|foreach|while)\s*(\(.+?\))?\s*\}#i';

    /**
     * The regular expression to match control structure closings
     */
    const BLOCK_END_REGEX = '#\{\/(if|for|foreach|while)\s*\}#is';

    /**
     * The regular expression to echo a variable
     */
    const ECHO_REGEX = '#\{{2}\s*(.+?)\}{2}#is';

    /**
     * The regular expression for assignations
     */
    const ASSIGN_REGEX = '#\{assign\s+name=(["\'])(\w+)\\1\s*\}(.*?)\{\/assign\}#ims';

    /**
     * The regular expression to match views plugins
     */
    const PLUGIN_REGEX = '#\{(\w+)((\s+[\w\-]+\=([\'"])((?:[^\4\\\\]|\\\\.)*?)\4)*?)\s*\}#sm';

    /**
     * The regular expression to match view plugins attributes
     */
    const PLUGIN_ARGUMENTS_REGEX = '#([\w\-]+)\=([\'"])(\{?)((?:[^\2\\\\]|\\\\.)*?)(\}?)\\2#sm';

    /**
     * The regular expresison to match translations
     */
    const TRANSLATION_REGEX = '#{(?!if)([a-zA-Z]{2})}(.*?){/\\1}#ism';


    /**
     * Constructor
     *
     * @param string $file The template file to parse
     */
    public function __construct($file){
        if(!is_file($file)) {
            // The source file does not exist
            throw new ViewException(ViewException::TYPE_FILE_NOT_FOUND, $file);
        }

        $this->file = $file;

        // Get the cache file containing the precompiled
        $this->cacheFile = 'views/' . str_replace(array(ROOT_DIR, '/'), array('', '-'), realpath($this->file)) . '.php';

        if(! App::cache()->isCached($this->file, $this->cacheFile)) {
            $this->content = file_get_contents($this->file);
            $this->parse();
            App::cache()->save($this->cacheFile, $this->parsed);
        }

        self::$instances[realpath($this->file)] = $this;
    }


    /**
     * Set data to display in the view
     *
     * @param array $data The data to insert in the view. The keys of the data will become variables in the view
     *
     * @return View The view itself
     */
    public function setData($data = array()){
        $this->data = $data;
        return $this;
    }


    /**
     * Add data to display in the view
     *
     * @param array $data The data to add in the view
     *
     * @return View The view itself
     */
    public function addData($data = array()){
        $this->data = array_merge($this->data, $data);
        return $this;
    }


    /**
     * Get the data pushed in the view
     *
     * @return array The view data
     */
    public function getData(){
        return $this->data;
    }


    /**
     * Parse the view into a PHP file
     *
     * @return View The view itself, to permit chained expressions
     */
    private function parse(){
        // Parse PHP Structures
        $replaces = array(
            // structure starts
            self::BLOCK_START_REGEX => "<?php $1 $2 : ?>",

            // structures ends
            self::BLOCK_END_REGEX   => "<?php end$1; ?>",

            // echo
            self::ECHO_REGEX        => "<?= $1 ?>",

            // Support translations in views
            self::TRANSLATION_REGEX => "<?php if(LANGUAGE == '$1'): ?>$2<?php endif; ?>",

            // assign template part in variable
            self::ASSIGN_REGEX      => "<?php ob_start(); ?>$3<?php \$$2 = ob_get_clean(); ?>"
        );

        $this->parsed = preg_replace(array_keys($replaces), $replaces, $this->content);

        // Parse view plugins
        $this->parsed = $this->parsePlugin($this->parsed);

        $this->parsed = '<?php namespace ' . __NAMESPACE__ . '; ?>' . $this->parsed;

        return $this;
    }


    /**
     * Parse plugins in a view
     *
     * @param string $content  The content to parse
     * @param string $inPlugin Internal variable that indicates if the plugin is another plugin (as  attribute value)
     *
     * @return string The parsed PHP instruction to inject in the view compilation
     */
    private function parsePlugin($content, $inPlugin = false){
        return preg_replace_callback(
            self::PLUGIN_REGEX, function ($matches) use ($inPlugin) {
                list($l, $component, $arguments) = $matches;
                $componentClass = '\\Hawk\\View\\Plugins\\' . ucfirst($component);

                if(!class_exists($componentClass)) {
                    return $matches[0];
                }
                try{

                    $parameters = array();

                    while(preg_match(self::PLUGIN_ARGUMENTS_REGEX, $arguments, $m)){
                        list($whole, $name, $quote, $lbrace, $value, $rbrace) = $m;
                        if($lbrace && $rbrace) {
                            $subPlugin = $lbrace . stripslashes($value) . $rbrace;
                            if(preg_match(self::PLUGIN_REGEX, $subPlugin)) {
                                // The value is a view plugin
                                $value = $this->parsePlugin($subPlugin, true);
                            }

                            // That is a PHP expression to evaluate => nothing to do
                        }
                        else{
                            // The value is a static string
                            $value = '\'' .addcslashes($lbrace . $value . $rbrace, '\\\'') . '\'';
                        }

                        $parameters[$name] = '\'' . $name . '\' => ' . $value;

                        // Remove the argument from the arguments list
                        $arguments = str_replace($m[0], '', $arguments);
                    }

                    if(! $inPlugin) {
                        return '<?= new ' . $componentClass . '("' . $this->file . '", $_viewData, array(' . implode(',', $parameters) . ') ) ?>';
                    }
                    else{
                        return '(new ' . $componentClass . '("' . $this->file . '", $_viewData, array(' . implode(',', $parameters) . ')))->display()';
                    }
                }
                catch(\Exception $e){
                    return $matches[0];
                }
            }, $content
        );
    }


    /**
     * Replace the keys in the template and display it
     *
     * @return string The HTML result of the view, applied with the data
     */
    public function display(){
        extract($this->data);
        $_viewData = $this->data;
        ob_start();

        include App::cache()->getCacheFilePath($this->cacheFile);

        return ob_get_clean();
    }


    /**
     * Generate a view result
     *
     * @param string $file The filename of the template
     * @param array  $data The data to apply to the view
     *
     * @return string The HTML result of the view
     */
    public static function make($file, $data = array()){
        $view = new self($file);
        $view->setData($data);
        return $view->display();
    }


    /**
     * Generate a view from a string
     *
     * @param string $content The string of the origin template
     * @param array  $data    The data to apply to the view
     *
     * @return string The HTML result of the string
     */
    public static function makeFromString($content, $data = array()){
        $file = tempnam(TMP_DIR, '');
        file_put_contents($file, $content);

        // calculate the view
        $view = new self($file);
        $view->setData($data);
        $result = $view->display();

        // Remove temporary files
        unlink($file);
        App::cache()->clear($view->cacheFile);

        return $result;
    }
}




/**
 * This class describes the View exceptions
 *
 * @package Exceptions
 */
class ViewException extends \Exception{
    /**
     * Error type : source file not found
     */
    const TYPE_FILE_NOT_FOUND = 1;

    /**
     * Error type : view evaluation failed
     */
    const TYPE_EVAL = 2;

    /**
     * Constructor
     *
     * @param int       $type     The type of exception
     * @param string    $file     The source file that caused this exception
     * @param Exception $previous The previous exception that caused that one
     */
    public function __construct($type, $file, $previous = null){
        $code = $type;
        switch($type){
            case self::TYPE_FILE_NOT_FOUND:
                $message = "Error creating a view from template file $file : No such file or directory";
                break;

            case self::TYPE_EVAL:
                $trace = array_map(
                    function ($t) {
                        return $t['file'] . ':' . $t['line'];
                    }, $previous->getTrace()
                );

                $message = "An error occured while building the view from file $file : " . $previous->getMessage() . PHP_EOL . implode(PHP_EOL, $trace);
                break;
        }

        parent::__construct($message, $code, $previous);
    }
}
