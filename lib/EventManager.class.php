<?php

class EventManager{
    private static $events = array();
    const ORDER_LAST = -1;
    const ORDER_FIRST = -2;
    
    public static function on($name, $action, $order = self::ORDER_LAST ){
        if(preg_match('/ /', $name)){
            $names = explode(" ", $name);
            foreach($names as $n){
                self::on($n, $action, $order);
            }
        }
        else{
            switch($order){
                case self::ORDER_FIRST:
                    $key = empty(self::$events[$name]) ? 0 : min(array_keys(self::$events[$name])) - 1;
                    break;
            
                case self::ORDER_LAST :
                    $key = empty(self::$events[$name]) ? 1 : max(array_keys(self::$events[$name])) + 1;
                    break;
                
                default :
                    $key = $order;
                    while(isset(self::$events[$name][$key]))
                        $key ++;
                    break;
            }
            
            self::$events[$name][$key] = $action;
        }
    }
    
    public static function trigger($event){
        $name = $event->getName();

        $trace = debug_backtrace()[0];
        Log::debug('The event ' . $name . 'has been triggered from ' . $trace['file'] . ':' . $trace['line']);
        
        if(isset(self::$events[$name])){
            ksort(self::$events[$name]);
            foreach(self::$events[$name] as $action){				
                $action($event);
            }
        }
    }
}