<?php
/**
 * Route.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes the routes behavior
 * @package Core\Router
 */
class Route{
	use Utils;

	/**
	 * The route data, declared like '{dataname}' in the route definition
	 * @var array
	 */
	private $data = array();

	/**
	 * The necessary authentications to access the route
	 * @var array
	 */
	private $auth = array();


	/**
	 * The route URL
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
	 * @var array
	 */
	$where = array(),

	/**
	 * The default values of the route parameters
	 * @param array
	 */
	$default = array(),

	/**
	 * The route action
	 * @param string
	 */
	$action = '',

	/**
	 * The route pattern
	 * @param string
	 */
	$pattern = '';


	
	/**
	 * Constructor
	 * @param string $url The route URI pattern
	 * @param array $param The route parameters, containing the pattern rules, the default values, the action associated with this route
	 */
	public function __construct($url, $param){
		$this->map($param);
		
		$this->args = array();
		$this->url = $this->prefix . $url;				
		$this->pattern = preg_replace_callback("/\{(\w+)\}/", function($match){			
			$this->args[] = $match[1];
			$where = $this->where[$match[1]] ? $this->where[$match[1]] : '.*?';
			return "(" . $where . ")";
		}, $this->url);

		if($this->namespace){
			$this->action = $this->namespace . '\\' . $this->action;
		}
	}
	

	/**
	 * Check if the route pattern match with a given URI, and if it matches, set the route data
	 * @param string $uri The URI to check
	 * @return bool true if the URI match the route, else False
	 */
	public function match($uri){
		if(preg_match('~^' . $this->pattern . '/?$~i', $uri, $m)){
			// The URL match, let's test the filters to access this URL are OK					
			foreach(array_slice($m, 1) as $i => $var){
				$this->setData($this->args[$i], $var);
			}				
			return true;
				
		}
		return false;
	}
	

	/**
	 * Get the route data
	 * @param string $prop If set, the method will return the data value for this property, else it will return the whole route data
	 * @param mixed If $prop is set, the data value for this property, else the whole route data
	 */
	public function getData($prop = null){
		if(!$prop){
			return $this->data;
		}
		else{
			return $this->data[$prop];
		}
	}
	

	/**
	 * Set the route data
	 * @param string $key The property name of the data to set
	 * @param mixed $value The value to set
	 */
	public function setData($key, $value){
		$this->data[$key] = $value;
	}
	

	/**
	 * Get the route action
	 * @return string The action associated with the route, formatted like : '<ControllerClass>.<method>'
	 */
	public function getAction(){
		return $this->action;
	}
	

	/**
	 * Check of the route is accessible by the web client
	 * @return bool True if the route is accessible, False in other case
	 */
	public function isAccessible(){
		foreach($this->auth as $auth){
			if(!$auth){
				return false;
			}				
		}	
		return true;
	}
}