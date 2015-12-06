<?php
/**
 * Router.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class describes the application router. It is used in any plugin to route URIs to controllers methods
 * @package Core\Router
 */
class Router{
	/**
	 * Invalid URL. This URI is displayed when no URI was found for a given route name	 
	 */
	const INVALID_URL = '/INVALID_URL';

	/**
	 * The defined routes
	 * @var array
	 */
	private $routes = array(),	

	/**
	 * The routes accessible for the current request method
	 */
	$activeRoutes = array(),

	/**
	 * The current route, associated to the current URI
	 */
	$currentRoute,

	/**
	 * The authentications required to match the URIs
	 */
    $auth = array(),


    /**
     * The predefined data for the routes
     */
    $predefinedData = array();

    
    /**
     * The router instance
     */
    private static $instance;

    /**
     * Constrcutor
     */
    private function __construct(){}

    /**
     * Get the router instance
     */
    public static function getInstance(){
    	if(!isset(self::$instance)){
    		self::$instance = new self;
    	}

    	return self::$instance;
    }
    
	/**
	 * Add a new accessible route to the router
	 * @param string $method The HTTP method the route is accessible for
	 * @param string $name The route name. This name must be unique for each route
	 * @param string $uri The route URI, defined like : /path/{param1}/to/{param2}
	 * @param array $param The route parameters. This array can have the following data :
	 *						- 'action' string (required) : The controller method to call when the route is matched. formatted like this : 'ControllerClass.method'
	 *						- 'where' array (optionnal) : An array defining each parameter pattern, where keys are the names of the route parameters, and values are the regular expression to match (without delimiters)
	 *						- 'default' array (optionnal) : An array defining the default values of parameters. This is useful to generate a URI from a route name (method getUri), without giving all parameters values
	 */
	private function add($method, $name, $uri, $param){		
		if(isset($param['auth'])){
			$auth = $param['auth'];
			$param['auth'] = $this->auth;
			$param['auth'][] = $auth;
		}
		else{
			$param['auth'] = $this->auth;
		}

		foreach($this->predefinedData as $key => $value){
			$param[$key] = $value;
		}
		

		if(isset($this->routes[$name])){
			trigger_error("The route named '$name' already exists", E_USER_WARNING);
		}
		else{
			$route = new Route($name, $uri, $param);
					
			$this->routes[$name] = &$route;
			
			if(App::request()->getMethod() == $method || $method == 'any'){
				$this->activeRoutes[$name] = &$route;
			}
		}
	}

	/**
	 * Add an authentication condition to match the routes defined inside $action callback. For example, you can write something like :
	 * App::router()->auth(App::session()->getUser()->isAllowed('admin.all'), function(){
	 *		App::router()->get('test-route', '/test', array('action' => 'TestController.testMethod'));
	 * });
	 * If the user tries to access to /test without the necessary privileges, then a HTTP code 403 (Forbidden) will be returned
	 * @param boolean $auth The authentication. If true, then the routes inside are accessible, else they're not
	 * @param callable $action The function that defines the routes under this authentication
	 */
	public function auth($auth, $action){
		// Add the authentication for all following route
		$this->auth[] = $auth;

		// Exceute the action
		$action();
		
		// Remove the authentication for the rest of the scripts
		array_pop($this->auth);
	}


	/**
	 * Set properties for all the routes that are defined in the $action callback. 
	 * It can be used to set a prefix to a set of routes, a namespace for all routes actions, ...
	 * @param array $data The properties to set
	 * @param callable $action The function that defines the routes with these properties
	 */
	public function setProperties($data, $action){
		$currentData = $this->predefinedData;
		foreach($data as $key => $value){
			$this->predefinedData[$key] = $value;
		}
		
		$action();

		$this->predefinedData = $currentData;
	}
	

	/**
	 * Add a route acessible by GET HTTP requests
	 * @param string $name The route name. This name must be unique for each route
	 * @param string $uri The route URI, defined like : /path/{param1}/to/{param2}
	 * @param array $param The route parameters. This array can have the following data :
	 *						- 'action' string (required) : The controller method to call when the route is matched. formatted like this : 'ControllerClass.method'
	 *						- 'where' array (optionnal) : An array defining each parameter pattern, where keys are the names of the route parameters, and values are the regular expression to match (without delimiters)
	 *						- 'default' array (optionnal) : An array defining the default values of parameters. This is useful to generate a URI from a route name (method getUri), without giving all parameters values
	 */
	public function get($name, $url, $param){
		$this->add('get',$name, $url, $param);
	}
	

	/**
	 * Add a route acessible by POST HTTP requests
	 * @param string $name The route name. This name must be unique for each route
	 * @param string $uri The route URI, defined like : /path/{param1}/to/{param2}
	 * @param array $param The route parameters. This array can have the following data :
	 *						- 'action' string (required) : The controller method to call when the route is matched. formatted like this : 'ControllerClass.method'
	 *						- 'where' array (optionnal) : An array defining each parameter pattern, where keys are the names of the route parameters, and values are the regular expression to match (without delimiters)
	 *						- 'default' array (optionnal) : An array defining the default values of parameters. This is useful to generate a URI from a route name (method getUri), without giving all parameters values
	 */
	public function post($name, $url, $param){
		$this->add('post', $name, $url, $param);
	}


	/**
	 * Add a route acessible by DELETE HTTP requests
	 * @param string $name The route name. This name must be unique for each route
	 * @param string $uri The route URI, defined like : /path/{param1}/to/{param2}
	 * @param array $param The route parameters. This array can have the following data :
	 *						- 'action' string (required) : The controller method to call when the route is matched. formatted like this : 'ControllerClass.method'
	 *						- 'where' array (optionnal) : An array defining each parameter pattern, where keys are the names of the route parameters, and values are the regular expression to match (without delimiters)
	 *						- 'default' array (optionnal) : An array defining the default values of parameters. This is useful to generate a URI from a route name (method getUri), without giving all parameters values
	 */
	public function delete($name, $url, $param){
		$this->add('delete', $name, $url, $param);
	}
	

	/**
	 * Add a route acessible by PATCH HTTP requests
	 * @param string $name The route name. This name must be unique for each route
	 * @param string $uri The route URI, defined like : /path/{param1}/to/{param2}
	 * @param array $param The route parameters. This array can have the following data :
	 *						- 'action' string (required) : The controller method to call when the route is matched. formatted like this : 'ControllerClass.method'
	 *						- 'where' array (optionnal) : An array defining each parameter pattern, where keys are the names of the route parameters, and values are the regular expression to match (without delimiters)
	 *						- 'default' array (optionnal) : An array defining the default values of parameters. This is useful to generate a URI from a route name (method getUri), without giving all parameters values
	 */
	public function patch($name, $url, $param){
		$this->add('patch', $name, $url, $param);
	}

	/**
	 * Add a route acessible by GET, POST OR DELETE HTTP requests
	 * @param string $name The route name. This name must be unique for each route
	 * @param string $uri The route URI, defined like : /path/{param1}/to/{param2}
	 * @param array $param The route parameters. This array can have the following data :
	 *						- 'action' string (required) : The controller method to call when the route is matched. formatted like this : 'ControllerClass.method'
	 *						- 'where' array (optionnal) : An array defining each parameter pattern, where keys are the names of the route parameters, and values are the regular expression to match (without delimiters)
	 *						- 'default' array (optionnal) : An array defining the default values of parameters. This is useful to generate a URI from a route name (method getUri), without giving all parameters values
	 */
	public function any($name, $url, $param){
		$this->add('any', $name, $url, $param);		
	}
	

	/**
	 * Compute the routing, and execute the controller method associated to the URI	 
	 */
	public function route(){
		$uri = preg_replace("/\?.*$/", "", $this->getUri());

		// Scan each row
		foreach($this->activeRoutes as $route){
            if($route->match($uri)){                  	      	
            	// The URI matches with the route
            	if($route->isAccessible()){
            		// The route authentications are validated
					$this->currentRoute = &$route;
					
            		// Emit an event, saying the routing action is finished
					$event = new Event('after-routing', array(
						'route' => $route,						
					));
					$event->trigger();

					$route = $event->getData('route');

					list($classname, $method) = explode(".", $route->action);

					// call a controller method
					$controller = new $classname($route->getData());                              
					App::logger()->debug('URI ' . self::getUri() . ' has been routed => ' . $classname . '::' . $method);
	                
	                // Set the controller result to the HTTP response
					App::response()->setBody($controller->compute($method));
				}
				else{					

					// The route is not accessible
					App::logger()->warning('A user with the IP address ' . App::request()->clientIp() . ' tried to access ' . $this->getUri() . ' without the necessary privileges');
					App::response()->setStatus(403);
					$response = array(
						'message' => Lang::get('main.403-message'),
						'reason' => !App::session()->isConnected() ? 'login' : 'permission'
					);			

					App::response()->setContentType('json');
					App::response()->end($response);
				}
				return;
            }            
		}
		
		// The route was not found 
		App::logger()->warning('The URI ' . $this->getUri() . ' has not been routed');
		App::response()->setStatus(404);
        App::response()->setBody(Lang::get('main.404-message', array('uri' => $uri)));
	}
	

	/**
	 * Get all defined routes
	 * @return array The defined routes
	 */
	public function getRoutes(){
		return $this->routes;		
	}
    
    /**
     * Get the routes accessible for the current HTTP request method
     * @return array The list of the accessible routes
     */
    public function getActiveRoutes(){
        return $this->activeRoutes;
    }
	

	/**
	 * Get the route corresponding to the current HTTP request
	 * @return Route The current route
	 */
	public function getCurrentRoute(){
		return isset($this->currentRoute) ? $this->currentRoute : null;
	}
	
	/**
	 * Get the action parameter of the current route
	 * @return string The action of the current route
	 * @see Router::getCurrentRoute
	 */
	public function getCurrentAction(){
		return isset($this->currentRoute) ? $this->currentRoute->action : '';
	}

	/**
	 * Get the last instanciated controller
	 * @return Controller The last instanciated controller
	 */
	public function getCurrentController(){
		return Controller::$currentController;
	}
	
	/**
	 * Generate an URI from a given controller method (or route name) and its arguments. if $method is not set, then returns the current URI, relative to the site root URL
	 * @param string $name The route name of the controller method, formatted like this : 'ControllerClass.method'
	 * @param array $args The route arguments, where keys define the parameters names and values, the values to affect.
	 * @return string The generated URI, or the current URI (if $method is not set)
	 */
	public function getUri($name = '', $args= array()){
		if(!$name){
			$fullUrl = getenv('REQUEST_SCHEME') . '://' . getenv('SERVER_NAME') . getenv('REQUEST_URI');
			
			$rooturl = App::conf()->has('rooturl') ? App::conf()->get('rooturl') : getenv('REQUEST_SCHEME') . '://' . getenv('SERVER_NAME');
			
			return str_replace($rooturl, '', $fullUrl);
		}

		$route = $this->getRouteByAction($name);
				
		if(empty($route)){
			return self::INVALID_URL;
		}
		
		$url = $route->url;
		foreach($route->args as $arg){
			if(isset($args[$arg])){
				$replace = $args[$arg];
			}
			elseif(isset($route->default[$arg])){
				$replace = $route->default[$arg];
			}
			else{
				throw new \Exception("The URI built from '$method' needs the argument : $arg");
			}
			$url = str_replace("{{$arg}}", $replace, $url);
		}
		
		return $url;		
	}
    

    /**
	 * Generate a full URL from a given controller method (or route name) and its arguments. if $method is not set, then returns the current URL
	 * @param string $name The route name of the controller method, formatted like this : 'ControllerClass.method'
	 * @param array $args The route arguments, where keys define the parameters names and values, the values to affect.
	 * @return string The generated URI, or the current URI (if $method is not set)
	 * @see Router::getUri	 
	 */
    public function getUrl($name = '', $args = array()){
        return preg_replace('#/$#', '',App::conf()->get('rooturl')) . $this->getUri($name, $args);
    }


    /**
     * Get a route by action
     * @param string $name The route name of the controller method, formatted like this : 'ControllerClass.method'
	 * @param array $args The route arguments, where keys define the parameters names and values, the values to affect.
	 * @return Route The route corresponding to research
     */
    public function getRouteByAction($name){
    	$route = null;
		if(isset($this->routes[$name])){
			return $this->routes[$name];
		}
		
		return null;
    }


    /**
     * Find a route from an URI
     * @param string URI The uri to search the associated route
     * @return Route the found route
     */
    public function getRouteByUri($uri){
    	foreach($this->routes as $route){
    		if($route->match($uri)){
    			return $route;
    		}
    	}

    	return null;
    }
	
}