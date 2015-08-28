<?php
/**
 * Response.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

/**
 * This class defines static methods to prepare and send the HTTP response to the client 
 * @package Core
 */
class Response{
    /**
     * The response content
     * @var string|array
     */
    private static $content;

    /**
     * The response type
     * @var string
     */
    private static $type = 'html';
    

    /**
     * Get the content to return 
     * @return string
     */
    public static function get(){
        return self::$content;
    }
    

    /**
     * Set the content to return to the client
     * @param mixed $content The content to set
     */
    public static function set($content){
        self::$content = $content;        
    }

    /**
     * Set response headers
     */
    public function header($name, $value){
        header($name .': ' . $value);
    }

    /**
     * Set the content type of the HTTP response
     * @param string $type The type to set
     */
    public static function setContentType($type){
        Log::debug('change content type of response to ' . $type);
        header('Content-type: ' . $type);
    }

    /**
     * Set the response as JSON
     */
    public static function setJson(){        
        self::setContentType('application/json');
        self::$type = 'json';
    }
    
    /**
     * Set the response as JavaScript
     */
    public static function setScript(){
        self::setContentType('application/javascript');
        self::$type = 'script';
    }

    /**
     * Get the response type
     */
    public static function getType(){
        return self::$type;
    }
	

    /**
     * Set the response HTTP code
     * @param int $code The HTTP code to set
     */
	public static function setHttpCode($code){
		http_response_code($code);      
	}
    

    /**
     * Return the HTTP response to the client, ad exit the script
     */
	public static function end($content = ''){
        if($content){
            self::set($content);
        }
        
        switch(self::$type){
            case 'json' :
                echo json_encode(self::$content, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK);
            break;
            default :
                echo self::$content;
            break;
        }
        exit;
    }
	

    /**
     * Redirect to another URL
     * @param string $url The URL to redirect to
     */
	public static function redirect($url){
        Log::debug('redirect to ' . $url);
        header("Location: $url");
		exit;
	}
	
    /**
     * Redirect to a route
     * @param string $route The route name to redirect to
     * @param array $vars The route parameters value to set
     */
	public static function redirectToAction($route, $vars = array()){
		$url = Router::getUri($route, $vars = array());
		self::redirect($url);
	}
}