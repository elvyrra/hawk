<?php
/**
 * Event.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes script events that can be triggers and listened
 * @package Core
 */
class Event{
    /**
     * The event name
     * @var string
     */
    private $name, 

    /**
     * The event data
     * @var array
     */
    $data;

    /**
     * The listened events
     */
    private static $events = array();

    /**
     * Listener order as last 
     */
    const ORDER_LAST = -1;

    /**
     * Listener order as first
     */
    const ORDER_FIRST = -2;

    
    /**
     * Constructor. Create a new event
     * @param string $name The event name
     * @param array $data The event initial data
     */
    public function __construct($name, $data = array()){
        $this->name = $name;
        $this->data = $data;
    }
    

    /**
     * Get the event name
     * @return string The event name
     */
    public function getName(){
        return $this->name;
    }
    

    /**
     * Get event data. This method is used to get all the data of the event, or a specific data property
     * @param string $prop If set, this method will return the data property, else return all the event data
     * @return mixed The event data
     */
    public function getData($prop = null){
        return $prop ? $this->data[$prop] : $this->data;        
    }
    

    /**
     * Set data to the event
     * @param string $prop The property name to set in the data
     * @param mixed $value The value to set
     */
    public function setData($prop, $value){
        $this->data[$prop] = $value;
    }  


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
     * Remove all listeners on a event type
     * @param string $name The event name
     */
    public static function unbind($name){
        if(isset(self::$events[$name])){
            unset(self::$events[$name]);
        }
    }
    

    /**
     * Trigger an event
     * @param Event $event The event to trigger
     */
    public function trigger(){
        $name = $this->getName();

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        App::logger()->debug('The event ' . $name . ' has been triggered from ' . $trace['file'] . ':' . $trace['line']);

        if(isset(self::$events[$name])){
            ksort(self::$events[$name]);
            foreach(self::$events[$name] as $action){               
                $action($this);
            }
        }
    } 
}