<?php
/**
 * Router.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the application router. It is used in any plugin to route URIs to controllers methods
 *
 * @package Core\Router
 */
final class Router extends Singleton{
    /**
     * Invalid URL. This URI is displayed when no URI was found for a given route name
     */
    const INVALID_URL = '/INVALID_URL';

    /**
     * The defined routes
     *
     * @var array
     */
    private $routes = array(),

    /**
     * The current route, associated to the current URI
     */
    $currentRoute,

    /**
     * The current controller instance, associated to the current uri
     */
    $currentController,

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
    protected static $instance;

    /**
     * Add a new accessible route to the router
     *
     * @param string $method The HTTP method the route is accessible for
     * @param string $name   The route name. This name must be unique for each route
     * @param string $uri    The route URI, defined like : /path/{param1}/to/{param2}
     * @param array  $param  The route parameters. This array can have the following data :
     *                       <ul>
     *                           <li>'action' string (required) : The controller method to call when the route is matched,
     *                               formatted like this : 'ControllerClass.method'
     *                           </li>
     *                           <li>'where' array (optionnal) : An array defining each parameter pattern,
     *                               where keys are the names of the route parameters,
     *                               and values are the regular expression to match (without delimiters).
     *                           </li>
     *                           <li>'default' array (optionnal) : An array defining the default values of parameters.
     *                               This is useful to generate a URI from a route name (method getUri),
     *                               without giving all parameters values
     *                           </li>
     *                       </ul>
     */
    private function add($method, $name, $uri, $param) {
        if(isset($param['auth'])) {
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

        if(!isset($param['namespace'])) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $param['namespace'] = Plugin::getFilePlugin($trace[1]['file'])->getNamespace();
        }


        if(isset($this->routes[$name])) {
            trigger_error("The route named '$name' already exists", E_USER_WARNING);
        }
        else {
            $methods = $method === 'any' ? [] : [$method];

            $route = new Route($name, $uri, $methods, $param);

            $this->routes[$name] = &$route;
        }
    }

    /**
     * Add an authentication condition to match the routes defined inside $action callback.
     * For example, you can write something like :
     *
     * <code>
     * App::router()->auth(App::session()->getUser()->isAllowed('admin.all'), function(){
     *        App::router()->get('test-route', '/test', array('action' => 'TestController.testMethod'));
     * });
     * </code>
     *
     * If the user tries to access to /test without the necessary privileges,
     * then a HTTP code 403 (Forbidden) will be returned
     *
     * @param boolean  $auth   The authentication. If true, then the routes inside are accessible, else they're not
     * @param callable $action The function that defines the routes under this authentication
     */
    public function auth($auth, $action){
        // Add the authentication for all following route
        $this->auth[] = $auth;

        // Execute the action
        $action();

        // Remove the authentication for the rest of the scripts
        array_pop($this->auth);
    }


    /**
     * Set properties for all the routes that are defined in the $action callback.
     * It can be used to set a prefix to a set of routes, a namespace for all routes actions, ...
     *
     * @param array    $data   The properties to set
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
     * Set a prefix to routes URIs that are defined in $action callback
     *
     * @param string   $prefix The prefix to set to the URIs
     * @param callable $action The function that defined the routes with this prefix
     */
    public function prefix($prefix, $action) {
        $currentPrefix = empty($this->predefinedData['prefix']) ? '' : $this->predefinedData['prefix'];

        $this->setProperties(array('prefix' => $currentPrefix . $prefix), $action);
    }


    /**
     * Add a route acessible by GET HTTP requests
     *
     * @param string $name  The route name. This name must be unique for each route
     * @param string $path  The route path, defined like : /path/{param1}/to/{param2}
     * @param array  $param The route parameters. This array can have the following data :
     *                       <ul>
     *                           <li>'action' string (required) : The controller method to call when the route is matched,
     *                               formatted like this : 'ControllerClass.method'
     *                           </li>
     *                           <li>'where' array (optionnal) : An array defining each parameter pattern,
     *                               where keys are the names of the route parameters,
     *                               and values are the regular expression to match (without delimiters).
     *                           </li>
     *                           <li>'default' array (optionnal) : An array defining the default values of parameters.
     *                               This is useful to generate a URI from a route name (method getUri),
     *                               without giving all parameters values
     *                           </li>
     *                       </ul>
     */
    public function get($name, $path, $param){
        $this->add('get', $name, $path, $param);
    }


    /**
     * Add a route acessible by POST HTTP requests
     *
     * @param string $name  The route name. This name must be unique for each route
     * @param string $path  The route path, defined like : /path/{param1}/to/{param2}
     * @param array  $param The route parameters. This array can have the following data :
     *                       <ul>
     *                           <li>'action' string (required) : The controller method to call when the route is matched,
     *                               formatted like this : 'ControllerClass.method'
     *                           </li>
     *                           <li>'where' array (optionnal) : An array defining each parameter pattern,
     *                               where keys are the names of the route parameters,
     *                               and values are the regular expression to match (without delimiters).
     *                           </li>
     *                           <li>'default' array (optionnal) : An array defining the default values of parameters.
     *                               This is useful to generate a URI from a route name (method getUri),
     *                               without giving all parameters values
     *                           </li>
     *                       </ul>
     */
    public function post($name, $path, $param){
        $this->add('post', $name, $path, $param);
    }


    /**
     * Add a route acessible by PUT HTTP requests
     *
     * @param string $name  The route name. This name must be unique for each route
     * @param string $path  The route path, defined like : /path/{param1}/to/{param2}
     * @param array  $param The route parameters. This array can have the following data :
     *                       <ul>
     *                           <li>'action' string (required) : The controller method to call when the route is matched,
     *                               formatted like this : 'ControllerClass.method'
     *                           </li>
     *                           <li>'where' array (optionnal) : An array defining each parameter pattern,
     *                               where keys are the names of the route parameters,
     *                               and values are the regular expression to match (without delimiters).
     *                           </li>
     *                           <li>'default' array (optionnal) : An array defining the default values of parameters.
     *                               This is useful to generate a URI from a route name (method getUri),
     *                               without giving all parameters values
     *                           </li>
     *                       </ul>
     */
    public function put($name, $path, $param){
        $this->add('put', $name, $path, $param);
    }


    /**
     * Add a route acessible by DELETE HTTP requests
     *
     * @param string $name  The route name. This name must be unique for each route
     * @param string $path  The route path, defined like : /path/{param1}/to/{param2}
     * @param array  $param The route parameters. This array can have the following data :
     *                       <ul>
     *                           <li>'action' string (required) : The controller method to call when the route is matched,
     *                               formatted like this : 'ControllerClass.method'
     *                           </li>
     *                           <li>'where' array (optionnal) : An array defining each parameter pattern,
     *                               where keys are the names of the route parameters,
     *                               and values are the regular expression to match (without delimiters).
     *                           </li>
     *                           <li>'default' array (optionnal) : An array defining the default values of parameters.
     *                               This is useful to generate a URI from a route name (method getUri),
     *                               without giving all parameters values
     *                           </li>
     *                       </ul>
     */
    public function delete($name, $path, $param){
        $this->add('delete', $name, $path, $param);
    }


    /**
     * Add a route acessible by PATCH HTTP requests
     *
     * @param string $name  The route name. This name must be unique for each route
     * @param string $path  The route path, defined like : /path/{param1}/to/{param2}
     * @param array  $param The route parameters. This array can have the following data :
     *                       <ul>
     *                           <li>'action' string (required) : The controller method to call when the route is matched,
     *                               formatted like this : 'ControllerClass.method'
     *                           </li>
     *                           <li>'where' array (optionnal) : An array defining each parameter pattern,
     *                               where keys are the names of the route parameters,
     *                               and values are the regular expression to match (without delimiters).
     *                           </li>
     *                           <li>'default' array (optionnal) : An array defining the default values of parameters.
     *                               This is useful to generate a URI from a route name (method getUri),
     *                               without giving all parameters values
     *                           </li>
     *                       </ul>
     */
    public function patch($name, $path, $param){
        $this->add('patch', $name, $path, $param);
    }

    /**
     * Add a route acessible by GET, POST OR DELETE HTTP requests
     *
     * @param string $name  The route name. This name must be unique for each route
     * @param string $path  The route path, defined like : /path/{param1}/to/{param2}
     * @param array  $param The route parameters. This array can have the following data :
     *                       <ul>
     *                           <li>'action' string (required) : The controller method to call when the route is matched,
     *                               formatted like this : 'ControllerClass.method'
     *                           </li>
     *                           <li>'where' array (optionnal) : An array defining each parameter pattern,
     *                               where keys are the names of the route parameters,
     *                               and values are the regular expression to match (without delimiters).
     *                           </li>
     *                           <li>'default' array (optionnal) : An array defining the default values of parameters.
     *                               This is useful to generate a URI from a route name (method getUri),
     *                               without giving all parameters values
     *                           </li>
     *                       </ul>
     */
    public function any($name, $path, $param){
        $this->add('any', $name, $path, $param);
    }


    /**
     * Compute the routing, and execute the controller method associated to the URI
     */
    public function route(){
        $path = str_replace(BASE_PATH, '', parse_url(App::request()->getUri(), PHP_URL_PATH));

        // Scan each row
        foreach($this->routes as $route){
            if($route->match($path)) {
                // The URI matches with the route
                $this->currentRoute = &$route;

                // Check if the route is accessible with the current method
                if(!$route->isCallableBy(App::request()->getMethod())) {
                    throw new BadMethodException($route->url, App::request()->getMethod());
                }

                // Emit an event, saying the routing action is finished
                $event = new Event('after-routing', array(
                    'route' => $route,
                ));
                $event->trigger();

                $route = $event->getData('route');

                if(!$route->isAccessible()) {
                    // The route is not accessible
                    App::logger()->warning(sprintf(
                        'A user with the IP address %s tried to access %s without the necessary privileges',
                        App::request()->clientIp(),
                        App::request()->getUri()
                    ));

                    if(!App::session()->isLogged()) {
                        throw new UnauthorizedException();
                    }
                    else {
                        throw new ForbiddenException();
                    }
                }

                // The route authentications are validated
                list($classname, $method) = explode(".", $route->action);

                // call a controller method
                $this->currentController = $classname::getInstance($route->getData());
                App::logger()->debug(sprintf(
                    'URI %s has been routed => %s::%s',
                    App::request()->getUri(),
                    $classname,
                    $method
                ));

                // Set the controller result to the HTTP response
                App::response()->setBody($this->currentController->$method());

                return;
            }
        }

        App::logger()->warning('The URI ' . App::request()->getUri() . ' has not been routed');
        throw new PageNotFoundException();
    }


    /**
     * Get all defined routes
     *
     * @return array The defined routes
     */
    public function getRoutes(){
        return $this->routes;
    }

    /**
     * Get the route corresponding to the current HTTP request
     *
     * @return Route The current route
     */
    public function getCurrentRoute(){
        return isset($this->currentRoute) ? $this->currentRoute : null;
    }

    /**
     * Get the action parameter of the current route
     *
     * @return string The action of the current route
     */
    public function getCurrentAction(){
        return isset($this->currentRoute) ? $this->currentRoute->action : '';
    }

    /**
     * Get the last instanciated controller
     *
     * @return Controller The last instanciated controller
     */
    public function getCurrentController(){
        return $this->currentController;
    }

    /**
     * Generate an URI from a given controller method (or route name) and its arguments.
     *
     * @param string $name        The route name of the controller method, formatted like this : 'ControllerClass.method'
     * @param array  $args        The route arguments, where keys define the parameters names and values, the values to affect.
     * @param array  $queryString Query string parameters
     *
     * @return string The generated URI, or the current URI (if $method is not set)
     */
    public function getUri($name, $args= array(), $queryString = array()){

        $route = $this->getRouteByAction($name);

        if(empty($route)) {
            return self::INVALID_URL;
        }

        $url = $route->url;
        foreach($route->args as $arg){
            if(isset($args[$arg])) {
                $replace = $args[$arg];
            }
            elseif(isset($route->default[$arg])) {
                $replace = $route->default[$arg];
            }
            else{
                throw new \Exception("The URI built from '$name' needs the argument : $arg");
            }
            $url = str_replace("{{$arg}}", $replace, $url);
        }

        if(!empty($queryString)) {
            $url .= '?' . http_build_query($queryString);
        }

        return BASE_PATH . $url;
    }


    /**
     * Generate a full URL from a given controller method (or route name) and its arguments.
     *
     * @param string $name The route name of the controller method, formatted like this : 'ControllerClass.method'
     * @param array  $args The route arguments, where keys define the parameters names and values, the values to affect.
     *
     * @return string The generated URI, or the current URI (if $method is not set)
     */
    public function getUrl($name = '', $args = array()){
        return ROOT_URL . $this->getUri($name, $args);
    }


    /**
     * Get a route by action
     *
     * @param string $name The route name of the controller method, formatted like this : 'ControllerClass.method'
     *
     * @return Route The route corresponding to research
     */
    public function getRouteByAction($name){
        $route = null;
        if(isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        return null;
    }


    /**
     * Find a route from an URI
     *
     * @param string $path The path to search the associated route
     *
     * @return Route the found route
     */
    public function getRouteByUri($path){
        foreach($this->routes as $route){
            if($route->match($path)) {
                return $route;
            }
        }

        return null;
    }


    /**
     * Get a route by it name
     *
     * @param string $name The route name
     *
     * @return Route The found route
     */
    public function getRouteByName($name){
        return isset($this->routes[$name]) ? $this->routes[$name] : null;
    }

}