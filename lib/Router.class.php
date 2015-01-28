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
	private static $controllerRoutes = array();	
	private static $not_found_script = "";
	public static $data = array();
	public static $paths = array(); // This array contains the paths where to find controllers
	public static $before = array();
	public static $unless = array();
    private static $widgets = array();
    
    
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
	public static function add($type, $url, $param){
		$route = new Route($url, $param);
		self::$routes[] = &$route;
		self::$controllerRoutes[$route->action] = $route->originalUrl;
		
		if(Request::method() == $type)
			self::$activeRoutes[] = &$route;
	}
	
	public static function get($url, $param){
		self::add('get', $url, $param);
	}
	
	public static function post($url, $param){
		self::add('post', $url, $param);
	}
	
	public static function any($url, $param){
		self::post($url, $param);
		self::get($url, $param);
	}
	
	public static function notFound($script){
		self::$not_found_script = $script;		
	}
	
	public static function route(){
		$uri = preg_replace("/\?.*$/", "", Request::uri());
		foreach(self::$activeRoutes as $route){
            if($route->match($uri)){
				list($classname, $method) = explode(".", $route->action);
                
                
                // call a controller method
                $controller = new $classname($route->getData());                              
                Response::set($controller->_call($method));
				return;
            }            
		}
		
		// The route was not found 
		$controller = new MainController(array('uri' => $uri));
        Response::set($controller->_call('page404'));
	}
	
	public static function getRoutes(){
		return self::$routes;		
	}
    
    public static function getActiveRoutes(){
        return self::$activeRoutes;
    }
	
	// Generate a route from a given controller method and its arguments	
	public static function getUri($method, $args= array()){		
		$url = self::$controllerRoutes[$method];
		if(empty($url))
			return "/INVALID_URL";
		
		foreach($args as $arg => $value){
			$url = str_replace("{$arg}", $value, $url);			
		}
		return $url;		
	}
	
}