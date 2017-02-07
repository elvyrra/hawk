<?php
/**
 * Controller.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the behavior of a controller. All controllers defined in application plugins
 * must extend this class for the application routes work.
 *
 * @package Core
 */
class Controller{
    use Utils;

    /**
     * The current used controller. This static property is used to know which is the current controller associated to the current route
     *
     * @var Controller
     */
    public static $currentInstance = null;

    /**
     * Constant used in events triggered before a controller method to be executed
     */
    const BEFORE_ACTION = 'before';

    /**
     * Constant used in events triggered afrer a controller method has been executed
     */
    const AFTER_ACTION = 'after';

    /**
     * The plugin instance the controller is contained in
     *
     * @var Plugin
     */
    private $_pluginInstance;

    /**
     * The plugin name the controller is contained in
     *
     * @var string
     */
    public $_plugin;

    /**
     * This variable is set when a controller method is executed
     *
     * @var string
     */
    public $executingMethod = null;


    /**
     * Constructor
     *
     * @param array $param The parameters of the controller.
     *                     This parameter is set by the router with the parameters defined in the routes as '{paramName}'
     */
    protected function __construct($param = array()){
        $this->map($param);

        $this->_plugin = $this->getPlugin()->getName();

        self::$currentInstance = $this;

        $this->clone = clone $this;
    }

    /**
     * Get a controller instance
     *
     * @param array $param The parameters to send to the controller instance
     *
     * @return Controller The controller instance
     */
    public final static function getInstance($param = array()){
        return new ControllerProcessor(new static($param));
    }


    /**
     * Get the current controller instance
     *
     * @return Controller The current controller
     */
    public final static function current(){
        return self::$currentInstance;
    }


    /**
     * Add static content at start of the DOM
     *
     * @param string $content The content to add
     */
    private final function addContentAtStart($content) {
        $method = $this->executingMethod;

        Event::on(
            $this->_plugin . '.' . $this->getClassname() . '.' . $method . '.' . self::AFTER_ACTION, function ($event) use ($content) {
                if(App::response()->getContentType() === 'html') {
                    $html = $event->getData('result');
                    $bodyRegex = '#(<body.*?>)#';

                    if(preg_match($bodyRegex, $html)) {
                        $html = preg_replace($bodyRegex, "$1$content", $html);
                    }
                    else {
                        $html = $content . $html;
                    }

                    $event->setData('result', $html);
                }
            }
        );
    }

    /**
     * Add static content at the end of the DOM
     *
     * @param string $content The content to add
     */
    private final function addContentAtEnd($content) {
        $method = $this->executingMethod;

        Event::on(
            $this->_plugin . '.' . $this->getClassname() . '.' . $method . '.' . self::AFTER_ACTION, function ($event) use ($content) {
                if(App::response()->getContentType() === 'html') {
                    $html = $event->getData('result');
                    $closingBodyTag = '</body>';
                    $bodyRegex = '#' . preg_quote($closingBodyTag) . '#';

                    if(preg_match($bodyRegex, $html)) {
                        $html = preg_replace($bodyRegex, $content . $closingBodyTag, $html);
                    }
                    else {
                        $html .= $content;
                    }

                    $event->setData('result', $html);
                }
            }
        );
    }


    /**
     * Add a link tag for CSS inclusion at the end of the HTML result to return to the client
     *
     * @param string $url The URL of the css file to load
     */
    public final function addCss($url){
        $this->addContentAtStart('<link rel="stylesheet" property="stylesheet" type="text/css" href="' . $url . '" />');
    }

    /**
     * Add inline CSS at the end of the HTML result to return to the client
     *
     * @param string $style The CSS code to insert
     */
    public final function addCssInline($style){
        $this->addContentAtStart('<style type="text/css">' . $style . '</style>');
    }

    /**
     * Add a script tag at the end of the HTML result to return to the client
     *
     * @param string $url The URL of the JavaScript file to load
     */
    public final function addJavaScript($url){
        $this->addContentAtEnd('<script type="text/javascript" src="' . $url . '"></script>');
    }


    /**
     * Add inline JavaScript code at the end of the HTML result to return to the client
     *
     * @param string $script The JavaScript code to insert
     */
    public final function addJavaScriptInline($script){
        $this->addContentAtEnd('<script type="text/javascript">' . $script . '</script>');
    }


    /**
     * Add language keys to be accessible by Javascript.
     * To add serveral keys, provide one key by argument
     * Example : $this->addKeysToJavascript('plugin.key1', 'plugin2.key2');
     *
     * @param string ...$keys The keys to add
     */
    public final function addKeysToJavascript(...$keys){
        $instructions = array();
        foreach($keys as $key){
            list($plugin, $langKey) = explode(".", $key);
            $instructions[] = 'Lang.set("' . $key . '", "'. addcslashes(Lang::get($key), '"') . '");';
        }

        $script = 'require(["app"], function(){ ' . implode('', $instructions) . '});';

        $this->addJavaScriptInline($script);
    }


    /**
     * Get the controller namespace
     *
     * @return string the controller namespace
     */
    public final function getNamespace(){
        $reflection = new \ReflectionClass(get_called_class());

        return $reflection->getNamespaceName();
    }


    /**
     * Get the controller class
     *
     * @return string the controller class
     */
    public final function getClassname(){
        $reflection = new \ReflectionClass(get_called_class());
        return $reflection->getShortName();
    }

    /**
     * Get the plugin contaning the controller
     *
     * @return Plugin The plugin contaning the controller
     */
    public final function getPlugin(){
        if(isset($this->_pluginInstance)) {
            return $this->_pluginInstance;
        }

        foreach (Plugin::getAll() as $plugin) {
            if($plugin->getNamespace() == $this->getNamespace()) {
                $this->_pluginInstance = &$plugin;
                return $plugin;
            }
        }

        return null;
    }
}

/**
 * This class is used to apply preprocessor and postprocessor to controller methods
 *
 * @package Core
 */
class ControllerProcessor extends Controller{
    /**
     * Create a new instance of ControllerProcessor
     *
     * @param Controller $object The controller instance that is wrapped in the processor
     */
    public function __construct($object) {
        $this->controller = $object;
    }

    /**
     * Call a method of the controller
     *
     * @param string $method    The method to call
     * @param array  $arguments The arguments of the method call
     *
     * @return mixed The result of the method call
     */
    public function __call($method, $arguments) {
        $this->controller->executingMethod = $method;

        /*** Load widgets before calling the controller method ***/
        $class = $this->controller->getClassname();

        $eventBasename = $this->controller->_plugin . '.' . $class . '.' . $method . '.';

        $event = new Event($eventBasename . Controller::BEFORE_ACTION, array('controller' => $this->controller));
        $event->trigger();

        /*** Call the controller method ***/
        $result = call_user_func_array(array($this->controller, $method), $arguments);
        // if(App::response()->getContentType() == 'html' && is_string($result)) {
        //     // Create a phpQuery object to be modified by event listeners (widgets)
        //     $result = \phpQuery::newDocument($result);
        // }

        /*** Load the widgets after calling the controller method ***/
        $event = new Event($eventBasename . Controller::AFTER_ACTION, array('controller' => $this->controller, 'result' => $result));
        $event->trigger();

        // Return the result of the action
        $result = $event->getData('result');
        $this->controller->executingMethod = null;
        if($result instanceof \phpQuery || $result instanceof \phpQueryObject) {
            return $result->htmlOuter();
        }
        else{
            return $result;
        }

    }
}