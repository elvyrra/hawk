<?php
/**
 * Request.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class define methods to get HTTP request information 
 * @package Core
 */
class Request{
    /**
     * The clientIp, registered as static variable, to avoid to calculate it each time
     * @static
     */
    private static $clientIp,

    /**
     * The request headers
     */
    $headers,

    /**
     * The request body
     */
    $body;

    /**
     * Get the HTTP request method
     * @static
     * @return string the HTTP request method
     */
    public static function getMethod(){        
        return strtolower(getenv('REQUEST_METHOD'));
    }
    

    /**
     * Get the HTTP request URI
     * @static
     * @return string The HTTP request URI 
     */
    public static function getUri(){
        return getenv('REQUEST_URI');
    }
    

    /**
     * Check if the request is an AJAX request
     * @static
     * @return true if the request is an AJAX request else false
     */
    public static function isAjax(){
        return strtolower(self::getHeaders('X-Requested-With')) === 'xmlhttprequest';
    }
    

    /**
     * Get the client IP address.
     * @static
     * @return string The IPV4 address of the client that performed the HTTP request
     */    
    public static function clientIp(){
        if(isset(self::$clientIp)){
            return self::$clientIp;
        }

        if (self::getHeaders('X-Forwarded-For')) {
            // The user is behind a proxy that transmit HTTP_X_FORWARDED_FOR header
            if ( ! preg_match('![0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}!', self::getHeaders('X-Forwarded-For')) ){
                // The format of HTTP_X_FORWARDED_FOR header is not correct
                self::$clientIp = getenv('REMOTE_ADDR');
                return self::$clientIp;
            }
            else{
                // Get the last public IP in HTTP_X_FORWARDED_FOR header
                $chain = explode(',', preg_replace('/[^0-9,.]/', '', self::getHeaders('X-Forwarded-For')));
                for($i = 0 ; $i <= count($chain); $i ++){
                    $ip = $chain[$i];

                    if((!preg_match("!^(192\.168|10\.0\.0)!", $ip) && $ip != "127.0.0.1") || $i == count($chain) - 1){
                        self::$clientIp = $ip;
                        return self::$clientIp;                        
                    }
                }
            }
        }
    
        // X-Forwarded-For header has not been transmitted, get the REMOTE_ADDR header
        self::$clientIp = getenv('REMOTE_ADDR');
        return self::$clientIp;
    }


    /**
     * This function returns the value of the variable $name in the request body, or all the body if $name is not provided
     * @param string $name The variable name
     * @return string|array The parameter value or all the body
     */
    public static function getBody($name = ""){
        if(!self::$body){
            if(self::getHeaders('Content-Type') === 'application/json'){
                self::$body = json_decode(file_get_contents('php://input'), true);
            }
            else{
                self::$body = $_POST;
            }
        }
        if($name){
            return isset(self::$body[$name]) ? self::$body[$name] : null;
        }
        else{            
            return self::$body;
        }
    }

    /**
     * Get the request uploaded files for the given name, or all files if $name is not provided
     * @param string $name The key in $_FILES to get
     * @return string|array The file or all files
     */
    public static function getFiles($name = ''){
        if($name){
            return isset($_FILES[$name]) ? $_FILES[$name] : array();
        }
        else{
            return $_FILES;
        }
    }

    /**
     * This function returns the value of the parameter $name, or all the parameters if $name is not provided
     * @param string $name The parameter name
     * @return string|array The parameter value or all the parameters
     */
    public static function getParams($name = ""){
        if($name){
            return isset($_GET[$name]) ? $_GET[$name] : null;
        }
        else{
            return $_GET;
        }
    }

    /**
     * This function returns the header value for the key $name, of all the headers if $name is not provided
     * @param string $name The header key
     * @return string|array The header value or all the headers
     */
    public static function getHeaders($name = ""){
        if(!isset(self::$headers)){
            self::$headers = getallheaders();
        }       

        if($name){
            return isset(self::$headers[$name]) ? self::$headers[$name] : null;
        }
        else{
            return self::$headers;
        }        
    }

    /**
     * This function returns the value of the cookie named $name, or all cookies if $name is not provided
     * @param string $name The cookie name
     * @return string|array The cookie value or all the cookies
     */
    public static function getCookies($name = ""){
        if($name){
            return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
        }
        else{
            return $_COOKIE;
        }
    }
}