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
     * Constructor
     *
     * @param array $param The parameters of the controller. T
     *                     his parameter is set by the router with the parameters defined in the routes as '{paramName}'
     */
    public function __construct($param = array()){
        $this->map($param);

        $this->_plugin = $this->getPlugin()->getName();

        self::$currentInstance = $this;
    }


    /**
     * Get a controller instance
     *
     * @param array $param The parameters to send to the controller instance
     *
     * @return Controller The controller instance
     */
    public static function getInstance($param = array()){
        return new static($param);
    }


    /**
     * Get the current controller instance
     *
     * @return Controller The current controller
     */
    public static function current(){
        return self::$currentInstance;
    }


    /**
     * Execute a controller method. This method is called by the router.
     * It execute the controller method, and triggers events before and after the method has been executed,
     * to add widgets or other functionnalities from another plugin than the controller's one.
     *
     * @param string $method The method to execute
     *
     * @return mixed The result of the controller method execution
     */
    public function compute($method){
        /*** Load widgets before calling the controller method ***/
        $class = $this->getClassname();

        $eventBasename = $this->_plugin . '.' . $class . '.' . $method . '.';

        $event = new Event($eventBasename . self::BEFORE_ACTION, array('controller' => $this));
        $event->trigger();


        /*** Call the controller method ***/
        $args = array_splice(func_get_args(), 1);
        $result = call_user_func_array(array($this, $method), $args);
        if(App::response()->getContentType() == 'html') {
            // Create a phpQuery object to be modified by event listeners (widgets)
            $result = \phpQuery::newDocument($result);
        }

        /*** Load the widgets after calling the controller method ***/
        $event = new Event($eventBasename . self::AFTER_ACTION, array('controller' => $this, 'result' => $result));
        $event->trigger();

        $result = $event->getData('result');
        if($result instanceof \phpQuery) {
            return $result->htmlOuter();
        }
        else{
            return $result;
        }
    }


    /**
     * Add static content at the end of the DOM
     *
     * @param string $content The content to add
     */
    private function addContentAtEnd($content){
        $action = App::router()->getCurrentAction();
        list($tmp, $method) = explode('.', $action);

        Event::on(
            $this->_plugin . '.' . $this->getClassname() . '.' . $method . '.' . self::AFTER_ACTION, function ($event) use ($content) {
                if(App::response()->getContentType() === 'html') {
                    $dom = $event->getData('result');
                    if($dom->find('body')->length) {
                        $dom->find('body')->append($content);
                    }
                    else{
                        $dom->find("*:first")->parent()->children()->slice(-1)->after($content);
                    }
                }
            }
        );
    }

    /**
     * Add a link tag for CSS inclusion at the end of the HTML result to return to the client
     *
     * @param string $url The URL of the css file to load
     */
    public function addCss($url){
        $this->addContentAtEnd("<link rel='stylesheet' property='stylesheet' type='text/css' href='$url' />");
    }

    /**
     * Add inline CSS at the end of the HTML result to return to the client
     *
     * @param string $style The CSS code to insert
     */
    public function addCssInline($style){
        $this->addContentAtEnd("<style type='text/css'>$style</style>");
    }

    /**
     * Add a script tag at the end of the HTML result to return to the client
     *
     * @param string $url The URL of the JavaScript file to load
     */
    public function addJavaScript($url){
        $this->addContentAtEnd("<script type='text/javascript' src='$url'></script>");
    }


    /**
     * Add inline JavaScript code at the end of the HTML result to return to the client
     *
     * @param string $script The JavaScript code to insert
     */
    public function addJavaScriptInline($script){
        $this->addContentAtEnd("<script type='text/javascript'>$script</script>");
    }


    /**
     * Add language keys to be accessible by Javascript.
     * To add serveral keys, provide one key by argument
     * Example : $this->addKeysToJavascript('plugin.key1', 'plugin2.key2');
     *
     * @param string ...$keys The keys to add
     */
    public function addKeysToJavascript(...$keys){
        $script = "";
        foreach($keys as $key){
            list($plugin, $langKey) = explode(".", $key);
            $script .= "Lang.set('$key', '" . addcslashes(Lang::get($key), "'") . "');";
        }

        $this->addJavaScriptInline($script);
    }


    /**
     * Get the controller namespace
     *
     * @return string the controller namespace
     */
    public function getNamespace(){
        $reflection = new \ReflectionClass(get_called_class());

        return $reflection->getNamespaceName();
    }


    /**
     * Get the controller class
     *
     * @return string the controller class
     */
    public function getClassname(){
        $reflection = new \ReflectionClass(get_called_class());
        return $reflection->getShortName();
    }

    /**
     * Get the plugin contaning the controller
     *
     * @return Plugin The plugin contaning the controller
     */
    public function getPlugin(){
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