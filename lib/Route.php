<?php
/**
 * Route.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the routes behavior
 *
 * @package Core\Router
 */
class Route{
    use Utils;

    /**
     * The route name
     *
     * @var string
     */
    private $name;

    /**
     * The route data, declared like '{dataname}' in the route definition
     *
     * @var array
     */
    private $data = array();

    /**
     * The necessary authentications to access the route
     *
     * @var array
     */
    private $auth = array();


    /**
     * The route URL
     *
     * @var string
     */
    public $url = '',

    /**
     * The route URL prefix
     */
    $prefix = '',

    /**
     * The action namespace
     */
    $namespace = '',

    /**
     * The pattern rules
     *
     * @var array
     */
    $where = array(),

    /**
     * The default values of the route parameters
     *
     * @param array
     */
    $default = array(),

    /**
     * The route action
     *
     * @param string
     */
    $action = '',

    /**
     * The route pattern
     *
     * @param string
     */
    $pattern = '',


    /**
     * The method this route can be accessed by
     */
    $methods = [];


    private static $SUPPORTED_METHODS = array(
        'get',
        'post',
        'patch',
        'put',
        'delete'
    );


    /**
     * Constructor
     *
     * @param string $name    The route name
     * @param string $url     The route URI pattern
     * @param array  $methods The methods the route is accessible
     * @param array  $param   The route parameters, containing the pattern rules,
     *                        the default values, the action associated with this route
     */
    public function __construct($name, $url, $methods, $param){
        $this->map($param);

        if(empty($methods)) {
            $methods = self::$SUPPORTED_METHODS;
        }

        foreach($methods as $method) {
            if(!in_array($method, self::$SUPPORTED_METHODS)) {
                throw new InternalErrorException('The route method ' . $method . ' is not supported');
            }
        }

        $this->methods = $methods;

        $this->name = $name;

        $this->args = array();
        $this->url = $this->prefix . $url;
        $this->pattern = preg_replace_callback(
            "/\{(\w+)\}/", function ($match) {
                $this->args[] = $match[1];
                $where = $this->where[$match[1]] ? $this->where[$match[1]] : '.*?';
                return "(" . $where . ")";
            }, $this->url
        );


        if($this->namespace) {
            $this->action = $this->namespace . '\\' . $this->action;
        }
    }


    /**
     * Get the route name
     *
     * @return string the route name
     */
    public function getName(){
        return $this->name;
    }


    /**
     * Check if the route pattern match with a given URI, and if it matches, set the route data
     *
     * @param string $path The URI to check
     *
     * @return bool true if the URI match the route, else False
     */
    public function match($path){
        if(preg_match('~^' . $this->pattern . '/?$~i', $path, $m)) {
            // The URL match, let's test the filters to access this URL are OK
            foreach(array_slice($m, 1) as $i => $var) {
                $this->setData($this->args[$i], is_numeric($var) ? (int) $var : $var);
            }
            return true;

        }
        return false;
    }


    /**
     * Get the route data
     *
     * @param string $prop If set, the method will return the data value for this property.
     *                     If not set, it will return the whole route data
     *
     * @return mixed If $prop is set, the data value for this property, else the whole route data
     */
    public function getData($prop = null) {
        if(!$prop) {
            return $this->data;
        }
        else{
            return isset($this->data[$prop]) ? $this->data[$prop] : null;
        }
    }


    /**
     * Set the route data
     *
     * @param string $key   The property name of the data to set
     * @param mixed  $value The value to set
     */
    public function setData($key, $value){
        $this->data[$key] = $value;
    }


    /**
     * Get the route action
     *
     * @return string The action associated with the route, formatted like : '<ControllerClass>.<method>'
     */
    public function getAction(){
        return $this->action;
    }


    /**
     * Get the route action controller class
     *
     * @return string The route action cotnroller class
     */
    public function getActionClassname(){
        list($controller, $method) = explode('.', $this->action);

        return $controller;
    }

    /**
     * Get the route action method name
     *
     * @return string The route action method name
     */
    public function getActionMethodName(){
        list($controller, $method) = explode('.', $this->action);

        return $method;
    }

    /**
     * Check is the route is callable by a method
     *
     * @param string $method The method to check (POST, GET, DELETE, ...)
     *
     * @return boolean True if the route is callable on the geivn method, else False
     */
    public function isCallableBy($method) {
        if(in_array($method, $this->methods)) {
            return true;
        }

        return false;
    }

    /**
     * Check of the route is accessible by the web client
     *
     * @return bool True if the route is accessible, False in other case
     */
    public function isAccessible() {
        foreach($this->auth as $auth) {
            if(is_callable($auth) && !$auth($this) || !$auth) {
                return false;
            }
        }
        return true;
    }
}