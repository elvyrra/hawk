<?php
/**
 * Event.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes script events that can be triggers and listened with the EventManager class
 * @package Core\Event
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
}