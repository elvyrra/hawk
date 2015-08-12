<?php
/**
 * Request.class.php
 * @author Elvyrra SAS
 */

/**
 * This class define methods to get HTTP request information 
 * @package Core
 */
class Request{
    /**
     * The clientIp, registered as static variable, to avoid to calculate it each time
     * @static
     */
    private static $clientIp;

    /**
     * Get the HTTP request method
     * @static
     * @return string the HTTP request method
     */
    public static function method(){        
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    

    /**
     * Get the HTTP request URI
     * @static
     * @return string The HTTP request URI 
     */
    public static function uri(){
        return $_SERVER['REQUEST_URI'];
    }
    

    /**
     * Check if the request is an AJAX request
     * @static
     * @return true if the request is an AJAX request else false
     */
    public static function isAjax(){
        return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST' );
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

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // The user is behind a proxy that transmit HTTP_X_FORWARDED_FOR header
            if ( ! preg_match('![0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}!', $_SERVER['HTTP_X_FORWARDED_FOR']) ){
                // The format of HTTP_X_FORWARDED_FOR header is not correct
                self::$clientIp = $_SERVER['REMOTE_ADDR'];
                return self::$clientIp;
            }
            else{
                // Get the last public IP in HTTP_X_FORWARDED_FOR header
                $chain = explode(',', preg_replace('/[^0-9,.]/', '', $_SERVER['HTTP_X_FORWARDED_FOR']));
                for($i  = count($chain) - 1; $i >= 0; $i --){
                    $ip = $chain[$i];

                    if((!preg_match("!^(192\.168|10\.0\.0)!", $ip) && $ip != "127.0.0.1") || $i == 0){                                    
                        self::$clientIp = $ip;
                        return self::$clientIp;
                        
                    }
                }
            }
        }
    
        // HTTP_X_FORWARDED_FOR header has not been transmitted, get the REMOTE_ADDR header
        self::$clientIp = $_SERVER['REMOTE_ADDR'];
        return self::$clientIp;
    }


    /**
     * Populate $_POST and $_FILES from php://input
     */
    public static function parseScriptInput(){
        $input = file_get_contents("php://input");

        if(!empty($input)){
            // Get data boundary
            preg_match("/^(\-{6}\w+)/i", $input, $m);
            $boundary = $m[1];

            // Remove first boundary to get only data
            $input = str_replace($boundary . '--', '', $input);

            // Put each data in an array
            $data = preg_split('/'.$boundary.'\r?\n/is', $input, -1, PREG_SPLIT_NO_EMPTY);

            $_POST = array();
            $_FILES = array();
            foreach($data as $field){
                if(preg_match('/^Content\-Disposition\: form\-data; name="(.+?)"; filename="(.+)"\r?\nContent\-Type: (.+?)\r?\n\r?\n(.*?)\r?\n$/is', $field, $match)){                              
                    $name = $match[1];
                    $filename = $match[2];
                    $mime = $match[3];
                    $content = $match[4];
                    $tmpname = uniqid('/tmp/');
                    file_put_contents($tmpname, $content);
                    if(preg_match('/^(\w+)\[(.*)\]$/', $name, $m)){
                        if(!isset($_FILES[$m[1]]))
                            $_FILES[$m[1]] = array();
                        if(empty($m[2])){
                            $_FILES[$m[1]]['name'][] = $filename;
                            $_FILES[$m[1]]['type'][] = $mime;
                            $_FILES[$m[1]]['tmp_name'][] = $tmpname;
                            $_FILES[$m[1]]['error'][] = UPLOAD_ERR_OK;
                            $_FILES[$m[1]]['size'][] = filesize($tmpname);
                        }
                        else{
                            $_FILES[$m[1]]['name'][$m[2]] = $filename;
                            $_FILES[$m[1]]['type'][$m[2]] = $mime;
                            $_FILES[$m[1]]['tmp_name'][$m[2]] = $tmpname;
                            $_FILES[$m[1]]['error'][$m[2]] = UPLOAD_ERR_OK;
                            $_FILES[$m[1]]['size'][$m[2]] = filesize($tmpname);
                        }
                    }
                    else{
                        $_FILES[$name]['name'] = $filename;
                        $_FILES[$name]['type'] = $mime;
                        $_FILES[$name]['tmp_name'] = $tmpname;
                        $_FILES[$name]['error'] = UPLOAD_ERR_OK;
                        $_FILES[$name]['size'] = filesize($tmpname);
                    }
                }
                elseif(preg_match('/^Content\-Disposition\: form\-data; name="(.+?)"\r?\n\r?\n(.*?)\r?\n$/is', $field, $match)){                
                    $name = $match[1];
                    $value = $match[2];             
                    if(preg_match('/^(\w+)\[(.*)\]$/', $name, $m)){
                        if(!isset($_POST[$m[1]]))
                            $_POST[$m[1]] = array();
                        if(empty($m[2])){
                            $_POST[$m[1]][] = $value;
                        }
                        else{
                            $_POST[$m[1]][$m[2]] = $value;
                        }
                    }
                    else{
                        $_POST[$name] = $value;
                    }
                }
            }
        }
    }
}