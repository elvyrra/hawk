<?php
/**
 * Response.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

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
     * The response headers
     */
    private static $headers = array();

    /**
     * The response cookies
     */
    private static $cookies = array();

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
     * @param string $name The header name
     * @param string $value The header value
     */
    public static function header($name, $value){        
        self::$headers[$name] = $value;
    }

    /**
     * Set response cookie
     * @param string $name The cookie name
     * @param mixed $data The cookie $data. Can be a string value, or an array containing the properties 'value', 'expires', 'path', 'domain', 'secure' or 'httponly'
     */
    public static function setCookie($name, $data){
        if(is_string($data)){
            $data = array(
                'value' => $data,                         
            );
        }

        if(empty($data['path'])){
            $data['path'] = '/';
        }

        self::$cookies[$name] = $data;
    }

    /**
     * Set the content type of the HTTP response
     * @param string $type The type to set
     */
    public static function setContentType($type){
        Log::debug('change content type of response to ' . $type);
        self::header('Content-type' , $type);
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
     * Return the HTTP response to the client, add exit the script
     * @param string $content The content to set in the response body before returning it to the client
     */
	public static function end($content = null){
        // Set the response cookies
        $lines = array();
        foreach(self::$cookies as $name => $data){
            $line = $name . '=' . $data['value'];
            if(!empty($data['expires'])){
                $line .= ';expires=' . gmdate('D, d M Y H:i:s \G\M\T', $data['expires']);
            }
            if(!empty($data['path'])){
                $line .= ';path=' . $data['path'];
            }
            if(!empty($data['domain'])){
                $line .= ';domain=' . $data['domain'];
            }
            if(!empty($data['secure'])){
                $line .= ';secure';
            }
            if(!empty($data['httponly'])){
                $line .= ';httponly';
            }

            $lines[] = $line;
        }
        if(!empty($lines)){
            self::header('Set-Cookie', implode(PHP_EOL, $lines));
        }


        // Set the response headers
        foreach(self::$headers as $name => $value){
            header($name .': ' . $value);
        }
        

        // Set the response body
        if($content !== null){
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
        exit();
    }
	

    /**
     * Redirect to another URL
     * @param string $url The URL to redirect to
     */
	public static function redirect($url){
        Log::debug('redirect to ' . $url);
        header("Location: $url");
		exit();
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