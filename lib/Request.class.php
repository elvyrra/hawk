<?php

class Request{
    public static function method(){
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    
    public static function uri(){
        return $_SERVER['REQUEST_URI'];
    }
    
    public static function isAjax(){
        return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST' );
    }
    
    public static function isPost(){
        return self::method() == 'post';
    }
    
    public static function clientIp(){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // L'utilisateur est derrière un proxy qui accepte le header HTTP_X_FORWARDED_FOR
            if ( ! preg_match('![0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}!', $_SERVER['HTTP_X_FORWARDED_FOR']) ){
                // Le format renvoyé par HTTP_X_FORWARDED_FOR n'est pas correct
                return $_SERVER['REMOTE_ADDR'];
            }
            else{

                /*** on récupère chaque IP renseignée dans HTTP_X_FORWARDED_FOR ***/
                $chain = explode(',', preg_replace('/[^0-9,.]/', '', $_SERVER['HTTP_X_FORWARDED_FOR']));
                for($i  = count($chain) - 1; $i >= 0; $i --){
                    $ip = $chain[$i];

                    if((!preg_match("!^(192\.168|10\.0\.0)!", $ip) && $ip != "127.0.0.1") || $i == 0){                                    
                        // L'adresse est une IP de réseau local, on ne retourne pas cette valeur
                        return $ip;
                    }
                }
            }
        }
    
        // le header HTTP_X_FORWARDED_FOR n'est pas supporté par le proxy de l'utilisateur ou aucune adresse non locale n'a été trouvée
        return $_SERVER['REMOTE_ADDR'];
    }

    public function redirect($url){
        header("Location: " . Router::getUri($url));
    }
}