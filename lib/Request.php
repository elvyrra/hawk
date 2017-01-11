<?php
/**
 * Request.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class define methods to get HTTP request information
 *
 * @package Core
 */
final class Request extends Singleton{
    /**
     * The clientIp, registered as static variable, to avoid to calculate it each time
     *
     * @static
     */
    public $clientIp,

    /**
     * The request URI
     *
     * @var string
     */
    $uri,

    /**
     * The full request URL
     *
     * @var string
     */
    $url,

    /**
     * The request unique id
     *
     * @var string
     */
    $uid,

    /**
     * The request method (GET, POST, PATCH, DELETE, PUT)
     *
     * @var string
     */
    $method,

    /**
     * The request parameters
     *
     * @var array
     */
    $params,

    /**
     * The request headers
     *
     * @var array
     */
    $headers,

    /**
     * The request body
     *
     * @var mixed
     */
    $body,

    /**
     * The uploaded files
     *
     * @var array
     */
    $files = array(),

    /**
     * The request sent cookies
     *
     * @var array
     */
    $cookies = array();

    /**
     * The request instance
     *
     * @var Request
     */
    protected static $instance;

    /**
     * Predefined content types
     *
     * @var array
     */
    private static $contentTypes = array(
        'html' => 'text/html',
        'json' => 'application/json',
        'javascript' => 'application/javascript',
        'css' => 'text/css',
        'text' => 'text/plain',
        'xml' => 'application/xml',
        'stream' => 'application/octet-stream'
    );


    /**
     * Constrcutor, initialize the instance with the HTTP request data
     */
    protected function __construct(){
        // Get the request method
        $this->method = strtolower(getenv('REQUEST_METHOD'));

        // Get the request uri
        $this->uri = getenv('REQUEST_URI');

        // Get the full request URL
        $this->url = getenv('REQUEST_SCHEME') . '://' . getenv('HTTP_HOST') . getenv('REQUEST_URI');

        // Generate a uniq id for the request
        $this->uid = uniqid();

        // Get the request headers
        $this->headers = getallheaders();

        // Get the request parameters
        $this->params = $_GET;

        // Retrieve the body
        if($this->getHeaders('Content-Type') === 'application/json') {
            $this->body = json_decode(file_get_contents('php://input'), true);
        }
        else {
            switch($this->getMethod()) {
                case 'get' :
                    $this->body = array();
                    break;

                case 'post' :
                    $this->body = $_POST;
                    break;
                default :
                    parse_str(file_get_contents('php://input'), $this->body);
                    break;
            }
        }

        // Retreive the client IP
        if ($this->getHeaders('X-Forwarded-For')) {
            // The user is behind a proxy that transmit HTTP_X_FORWARDED_FOR header
            if (! preg_match('![0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}!', $this->getHeaders('X-Forwarded-For'))) {
                // The format of HTTP_X_FORWARDED_FOR header is not correct
                $this->clientIp = getenv('REMOTE_ADDR');
            }
            else{
                // Get the last public IP in HTTP_X_FORWARDED_FOR header
                $chain = explode(',', preg_replace('/[^0-9,.]/', '', self::getHeaders('X-Forwarded-For')));
                for($i = 0 ; $i <= count($chain); $i ++){
                    $ip = $chain[$i];

                    if((!preg_match("!^(192\.168|10\.0\.0)!", $ip) && $ip != "127.0.0.1") || $i == count($chain) - 1) {
                        $this->clientIp = $ip;
                        break;
                    }
                }
            }
        }
        else{
            // X-Forwarded-For header has not been transmitted, get the REMOTE_ADDR header
            $this->clientIp = getenv('REMOTE_ADDR');
        }

        // Get the request uploaded files
        $this->files = $_FILES;

        // Get the request cookies
        $this->cookies = $_COOKIE;
    }


    /**
     * Get the HTTP request method
     *
     * @return string the HTTP request method
     */
    public function getMethod(){
        return $this->method;
    }


    /**
     * Get the HTTP request URI
     *
     * @return string The HTTP request URI
     */
    public function getUri(){
        return $this->uri;
    }


    /**
     * Get the HTTP request full URL
     *
     * @return string The HTTP request full URL
     */
    public function getFullUrl() {
        return $this->url;
    }


    /**
     * Check if the request is an AJAX request
     *
     * @return true if the request is an AJAX request else false
     */
    public function isAjax(){
        return strtolower($this->getHeaders('X-Requested-With')) === 'xmlhttprequest';
    }


    /**
     * Get the client IP address.
     *
     * @return string The IPV4 address of the client that performed the HTTP request
     */
    public function clientIp(){
        return $this->clientIp;
    }


    /**
     * This function returns the value of the variable $name in the request body, or all the body if $name is not provided
     *
     * @param string $name The variable name
     *
     * @return string|array The parameter value or all the body
     */
    public function getBody($name = ""){
        if($name) {
            return isset($this->body[$name]) ? $this->body[$name] : null;
        }
        else{
            return $this->body;
        }
    }

    /**
     * Get the request uploaded files for the given name, or all files if $name is not provided
     *
     * @param string $name The key in $_FILES to get
     *
     * @return string|array The file or all files
     */
    public function getFiles($name = ''){
        if($name) {
            return isset($this->files[$name]) ? $this->files[$name] : array();
        }
        else{
            return $this->files;
        }
    }

    /**
     * This function returns the value of the parameter $name, or all the parameters if $name is not provided
     *
     * @param string $name The parameter name
     *
     * @return string|array The parameter value or all the parameters
     */
    public function getParams($name = '') {
        if($name) {
            return isset($this->params[$name]) ? $this->params[$name] : '';
        }
        else{
            return $this->params;
        }
    }

    /**
     * This function returns the header value for the key $name, of all the headers if $name is not provided
     *
     * @param string $name The header key
     *
     * @return string|array The header value or all the headers
     */
    public function getHeaders($name = ''){
        if($name) {
            return isset($this->headers[$name]) ? $this->headers[$name] : '';
        }
        else {
            return $this->headers;
        }
    }

    /**
     * This function returns the value of the cookie named $name, or all cookies if $name is not provided
     *
     * @param string $name The cookie name
     *
     * @return string|array The cookie value or all the cookies
     */
    public function getCookies($name = '') {
        if($name) {
            return isset($this->cookies[$name]) ? $this->cookies[$name] : '';
        }
        else {
            return $this->cookies;
        }
    }


    /**
     * This function returns the content-type expected for the response, and sent by Accept request header
     *
     * @return string The wanted type
     */
    public function getWantedType() {
        $accepts = $this->getHeaders('Accept');

        $accept = trim(explode(',', $accepts)[0]);

        $key = array_search($accept, self::$contentTypes);

        return $key ? $key : $accept;
    }
}
