<?php

class Response{
    private static $content, $header;
    private static $type = 'html';
    
    public static function get(){
        return self::$content;
    }
    
    public static function set($content){
        self::$content = $content;        
    }

    public static function setContentType($type){
        header('Content-type: ' . $type);
    }

    public static function setJson(){
        header("Content-type: application/json");
        self::$type = 'json';
    }
    
    public static function setScript(){
        header("Content-type: application/javascript");
        self::$type = 'script';
    }

    public static function getType(){
        return self::$type;
    }
	
	public static function setHttpCode($code){
		http_response_code($code);      
	}
    
	public static function end(){
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
	
	public static function redirect($url){
		header("Location: $url");
		exit;
	}
	
	public function redirectToAction($action, $vars = array()){
		$url = Router::getUri($action, $vars = array());
		self::redirect($url);
	}
}