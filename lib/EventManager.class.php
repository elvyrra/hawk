<?php
/**
 * EventManager.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

/**
 * This class manages scripts events
 * @package Core\Event
 */
class EventManager{
    /**
     * The listened events
     */
    private static $events = array();

    const ORDER_LAST = -1;
    const ORDER_FIRST = -2;
    
    /**
     * Add a listener on a event type
     * @param string $name The event name
     * @param callable $action The action to perform when the event is triggered. This function take as argument the event itself
     * @param int $order The listener order. If set, all the listeners on the same event type are executed following their order
     */
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
    

    /**
     * Trigger an event
     * @param Event $event The event to trigger
     */
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