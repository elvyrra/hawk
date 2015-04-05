<?php
/**********************************************************************
 *    						Route.class.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
class Router{
	private static $routes = array();	
	private static $activeRoutes = array();	
	private static $controllers = array();
	private static $currentRoute;
	private static $not_found_script = "";
	public static $data = array();
	public static $paths = array(); // This array contains the paths where to find controllers
	public static $before = array();
	public static $unless = array();
    private static $widgets = array();
    private static $auth = array();
    
    
	/*
	 * Prototype : public static function add($type, $url, $param)
	 * Description : Add a valid URI in the set of uris
	 * @param : 
	 	- $type : 'get' or 'post'
		- $url : The URI to catch, with format "/path{var1}/to/{var2}"
		- $param : An array of the parameters, containing the following data :
			o where : an array giving the regex of each variable in the URI
			o script : The script to execute
			o action : The controller method to execute, "Controller.method"
	 */
	public static function add($type,$name, $url, $param){		
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
			$route = new Route($url, $param);
					
			self::$routes[$name] = &$route;
			self::$controllers[$route->action] = &$route;
			
			if(Request::method() == $type || $type == 'any'){
				self::$activeRoutes[$name] = &$route;
			}
		}
	}

	public static function auth($auth, $action){
		self::$auth[] = $auth;
		$action();
		array_pop(self::$auth);
	}
	
	public static function get($name, $url, $param){
		self::add('get',$name, $url, $param);
	}
	
	public static function post($name, $url, $param){
		self::add('post', $name, $url, $param);
	}
	
	public static function any($name, $url, $param){
		self::add('any', $name, $url, $param);		
	}
	
	public static function route(){
		$uri = preg_replace("/\?.*$/", "", self::getUri());
		foreach(self::$activeRoutes as $route){
            if($route->match($uri)){                  	      	
            	if($route->isAuthValid()){
					self::$currentRoute = $route;
					list($classname, $method) = explode(".", $route->action);
	                
	                // call a controller method
					$controller = new $classname($route->getData());                              
	                
					Response::set($controller->_call($method));
				}
				else{					
					http_response_code(403);
					Response::set(Lang::get('main.403-message'));
				}
				return;
            }            
		}
		
		// The route was not found 
		http_response_code(404);
        Response::set(Lang::get('main.404-message', array('uri' => $uri)));
	}
	
	public static function getRoutes(){
		return self::$routes;		
	}
    
    public static function getActiveRoutes(){
        return self::$activeRoutes;
    }
	
	public static function getCurrentRoute(){
		return isset(self::$currentRoute) ? self::$currentRoute : null;
	}
	
	public static function getCurrentAction(){
		return isset(self::$currentRoute) ? self::$currentRoute->action : '';
	}
	
	// Generate a route from a given controller method and its arguments	
	public static function getUri($method = '', $args= array()){
		if(!$method){
			return $_SERVER['REQUEST_URI'];
		}

		$route = null;
		if(isset(self::$routes[$method])){
			$route = self::$routes[$method];
		}
		elseif(isset(self::$controllers[$method])){
			$route = self::$controllers[$method];
		}
		
		if(empty($route)){
			return "/INVALID_URL";
		}
		
		$url = $route->originalUrl;
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
	
}