<?php

class EventManager{
    private static $events = array();
    const ORDER_LAST = -1;
    const ORDER_FIRST = -2;
    
    public static function on($name, $action, $order = self::ORDER_LAST ){
        switch($order){
            case self::ORDER_FIRST:
                $key = min(array_keys(self::$events[$name])) - 1;
                break;
        
            case self::ORDER_LAST :
                $key = max(array_keys(self::$events[$name])) + 1;
                break;
            
            default :
                $key = $order;
                while(isset(self::$events[$name][$key]))
                    $key ++;
                break;
        }
        
        self::$events[$name][$key] = $action;
    }
    
    public static function trigger($event){
        $name = $event->getName();
        if(isset(self::$events[$name])){
            ksort(self::$events[$name]);
            foreach(self::$events[$name] as $action){
                $action($event);
            }
        }
    }
}