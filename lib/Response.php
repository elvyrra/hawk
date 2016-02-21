<?php
/**
 * Response.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class defines static methods to prepare and send the HTTP response to the client
 *
 * @package Core
 */
final class Response extends Singleton{
    /**
     * The response content
     *
     * @var string|array
     */
    private $body,

    /**
     * The response status
     *
     * @var int
     */
    $status = 200,

    /**
     * The response headers
     *
     * @var array
     */
    $headers = array(),

    /**
     * The response cookies
     *
     * @var array
     */
    $cookies = array(),

    /**
     * The response type
     *
     * @var string
     */
    $contentType = 'html';


    /**
     * The response instance
     *
     * @var Response
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
     * Constructor
     */
    protected function __construct(){
        $this->setContentType('html');
    }


    /**
     * Get the content to return
     *
     * @return string
     */
    public function getBody(){
        return $this->body;
    }


    /**
     * Set the content to return to the client
     *
     * @param mixed $body The content to set
     */
    public function setBody($body){
        $this->body = $body;
    }

    /**
     * Set response headers
     *
     * @param string $name  The header name
     * @param string $value The header value
     */
    public function header($name, $value){
        $this->headers[$name] = $value;
    }

    /**
     * Set response cookie
     *
     * @param string $name The cookie name
     * @param mixed  $data The cookie $data. Can be a string value, or an array containing the properties
     *                     'value', 'expires', 'path', 'domain', 'secure' or 'httponly'
     */
    public function setCookie($name, $data){
        if(is_string($data)) {
            $data = array(
                'value' => $data,
            );
        }

        if(empty($data['path'])) {
            $data['path'] = '/';
        }

        $this->cookies[$name] = $data;
    }

    /**
     * Set the content type of the HTTP response
     *
     * @param string $type The type to set
     */
    public function setContentType($type){
        App::logger()->debug('change content type of response to ' . $type);

        $this->contentType = $type;
        if(isset(self::$contentTypes[$type])) {
            $type = self::$contentTypes[$type];
        }
        $this->header('Content-type', $type);
    }


    /**
     * Set the response as HTML
     */
    public function setHtml(){
        $this->setContentType('html');
    }

    /**
     * Set the response as JSON
     */
    public function setJson(){
        $this->setContentType('json');
    }

    /**
     * Set the response as JavaScript
     */
    public function setScript(){
        $this->setContentType('javascript');
    }

    /**
     * Get the response type
     */
    public function getContentType(){
        return $this->contentType;
    }


    /**
     * Set the response HTTP code
     *
     * @param int $code The HTTP code to set
     */
    public function setStatus($code){
        $this->status = $code;
    }

    /**
     * Get the response HTTP status
     *
     * @return int
     */
    public function getStatus(){
        return $this->status;
    }



    /**
     * Return the HTTP response to the client, add exit the script
     *
     * @param string $content The content to set in the response body before returning it to the client
     */
    public function end($content = null){
        http_response_code($this->status);

        // Set the response cookies
        $lines = array();
        foreach($this->cookies as $name => $data){
            $line = $name . '=' . $data['value'];
            if(!empty($data['expires'])) {
                $line .= ';expires=' . gmdate('D, d M Y H:i:s \G\M\T', $data['expires']);
            }
            if(!empty($data['path'])) {
                $line .= ';path=' . $data['path'];
            }
            if(!empty($data['domain'])) {
                $line .= ';domain=' . $data['domain'];
            }
            if(!empty($data['secure'])) {
                $line .= ';secure';
            }
            if(!empty($data['httponly'])) {
                $line .= ';httponly';
            }

            $lines[] = $line;
        }
        if(!empty($lines)) {
            $this->header('Set-Cookie', implode(PHP_EOL, $lines));
        }


        // Set the response headers
        foreach($this->headers as $name => $value){
            header($name .': ' . $value);
        }


        // Set the response body
        if($content !== null) {
            $this->setBody($content);
        }


        switch($this->contentType){
            case 'json' :
                echo json_encode($this->body, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK);
                break;
            default :
                echo $this->body;
                break;
        }

        App::logger()->debug('script execution time : ' . ((microtime(true) - SCRIPT_START_TIME) * 1000) . ' ms');
        exit();
    }


    /**
     * Redirect to another URL
     *
     * @param string $url The URL to redirect to
     */
    public function redirect($url){
        App::logger()->debug('redirect to ' . $url);
        $this->header('Location', $url);
        $this->end();
    }

    /**
     * Redirect to a route
     *
     * @param string $route The route name to redirect to
     * @param array  $vars  The route parameters value to set
     */
    public function redirectToAction($route, $vars = array()){
        $url = App::router()->getUri($route, $vars = array());
        $this->redirect($url);
    }
}
