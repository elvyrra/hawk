<?php
/**
 * Router.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

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
	private static $routes = array();	

	/**
	 * The routes accessible for the current request method
	 */
	private static $activeRoutes = array();	

	/**
	 * The actions associated to each route
	 */
	private static $actions = array();

	/**
	 * The current route, associated to the current URI
	 */
	private static $currentRoute;

	/**
	 * The authentications required to match the URIs
	 */
    private static $auth = array();
    
    
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
	private static function add($method, $name, $uri, $param){		
		if(isset($param['auth'])){
			$auth = $param['auth'];
			$param['auth'] = self::$auth;
			$param['auth'][] = $auth;
		}
		else{
			$param['auth'] = self::$auth;
		}
		if(isset(self::$routes[$name])){
			trigger_error("The route named '$name' already exists", E_USER_WARNING);
		}
		else{
			$route = new Route($uri, $param);
					
			self::$routes[$name] = &$route;
			self::$actions[$route->action] = &$route;
			
			if(Request::method() == $method || $method == 'any'){
				self::$activeRoutes[$name] = &$route;
			}
		}
	}

	/**
	 * Add an authentication condition to match the routes defined inside $action callback. For example, you can write something like :
	 * Router::auth(Session::getUser()->isAuthorizedFor('admin.all'), function(){
	 *		Router::get('test-route', '/test', array('action' => 'TestController.testMethod'));
	 * });
	 * If the user tries to access to /test without the necessary privileges, then a HTTP code 403 (Forbidden) will be returned
	 * @param boolean $auth The authentication. If true, then the routes inside are accessible, else they're not
	 * @param callable $action The function that defines the routes under this authentication
	 */
	public static function auth($auth, $action){
		// Add the authentication for all following route
		self::$auth[] = $auth;

		// Exceute the action
		$action();
		
		// Remove the authentication for the rest of the scripts
		array_pop(self::$auth);
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
	public static function get($name, $url, $param){
		self::add('get',$name, $url, $param);
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
	public static function post($name, $url, $param){
		self::add('post', $name, $url, $param);
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
	public static function delete($name, $url, $param){
		self::add('delete', $name, $url, $param);
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
	public static function any($name, $url, $param){
		self::add('any', $name, $url, $param);		
	}
	

	/**
	 * Compute the routing, and execute the controller method associated to the URI	 
	 */
	public static function route(){
		$uri = preg_replace("/\?.*$/", "", self::getUri());
		
		// Scan each row
		foreach(self::$activeRoutes as $route){
            if($route->match($uri)){                  	      	
            	// The URI matches with the route
            	if($route->isAccessible()){

            		// The route authentications are validated
					self::$currentRoute = $route;
					list($classname, $method) = explode(".", $route->action);

					// call a controller method
					$controller = new $classname($route->getData());                              
					Log::debug('URI ' . self::getUri() . ' has been routed => ' . $classname . '::' . $method);

					// Emit an event, saying the routing action is finished
					$event = new Event('after-routing', array('controller' => $controller, 'method' => $method, 'args' => $route->getData()));
					EventManager::trigger($event);
	                
	                // Set the controller result to the HTTP response
					Response::set($controller->compute($method));
				}
				else{					

					// The route is not accessible
					Log::warning('A user with the IP address ' . Request::clientIp() . ' tried to access ' . self::getUri() . ' without the necessary privileges');
					http_response_code(403);					
					Response::set(Lang::get('main.403-message'));
				}
				return;
            }            
		}
		
		// The route was not found 
		Log::warning('The URI ' . self::getUri() . ' has not been routed');
		http_response_code(404);
        Response::set(Lang::get('main.404-message', array('uri' => $uri)));
	}
	

	/**
	 * Get all defined routes
	 * @return array The defined routes
	 */
	public static function getRoutes(){
		return self::$routes;		
	}
    
    /**
     * Get the routes accessible for the current HTTP request method
     * @return array The list of the accessible routes
     */
    public static function getActiveRoutes(){
        return self::$activeRoutes;
    }
	

	/**
	 * Get the route corresponding to the current HTTP request
	 * @return Route The current route
	 */
	public static function getCurrentRoute(){
		return isset(self::$currentRoute) ? self::$currentRoute : null;
	}
	
	/**
	 * Get the action parameter of the current route
	 * @return string The action of the current route
	 * @see Router::getCurrentRoute
	 */
	public static function getCurrentAction(){
		return isset(self::$currentRoute) ? self::$currentRoute->action : '';
	}

	/**
	 * Get the last instanciated controller
	 * @return Controller The last instanciated controller
	 */
	public function getCurrentController(){
		return Controller::$currentController;
	}
	
	/**
	 * Generate an URI from a given controller method (or route name) and its arguments. if $method is not set, then returns the current URI
	 * @param string $method The route name of the controller method, formatted like this : 'ControllerClass.method'
	 * @param array $args The route arguments, where keys define the parameters names and values, the values to affect.
	 * @return string The generated URI, or the current URI (if $method is not set)
	 */
	public static function getUri($method = '', $args= array()){
		if(!$method){
			$fullUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			
			$rooturl = Conf::has('rooturl') ? Conf::get('rooturl') : $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];
			
			return str_replace($rooturl, '', $fullUrl);
		}

		$route = self::getRouteByAction($method);
				
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
				throw new Exception("The URI built from '$method' needs the argument : $arg", E_USER_WARNING);
			}
			$url = str_replace("{{$arg}}", $replace, $url);
		}
		
		return $url;		
	}
    

    /**
	 * Generate a full URL from a given controller method (or route name) and its arguments. if $method is not set, then returns the current URL
	 * @param string $method The route name of the controller method, formatted like this : 'ControllerClass.method'
	 * @param array $args The route arguments, where keys define the parameters names and values, the values to affect.
	 * @return string The generated URI, or the current URI (if $method is not set)
	 * @see Router::getUri	 
	 */
    public static function getUrl($method = '', $args = array()){
        return preg_replace('#/$#', '',Conf::get('rooturl')) . self::getUri($method, $args);
    }


    /**
     * Get a route by action
     * @param string $method The route name of the controller method, formatted like this : 'ControllerClass.method'
	 * @param array $args The route arguments, where keys define the parameters names and values, the values to affect.
	 * @return Route The route corresponding to research
     */
    public static function getRouteByAction($method){
    	$route = null;
		if(isset(self::$routes[$method])){
			return self::$routes[$method];
		}
		elseif(isset(self::$actions[$method])){
			return self::$actions[$method];
		}
		
		return null;
    }
	
}