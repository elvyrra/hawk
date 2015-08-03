<?php


class Event{
    private $name, $data;
    
    public function __construct($name, $data = array()){
        $this->name = $name;
        $this->data = $data;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getData($prop = null){
        return $prop ? $this->data[$prop] : $this->data;        
    }
    
    public function setData($prop, $value){
        $this->data[$prop] = $value;
    }   
}