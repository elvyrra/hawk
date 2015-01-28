<?php

class Event{
    private $name, $data;
    
    public function __construct($name, $data){
        $this->name = $name;
        $this->data = $data;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getData($prop = null){
        return $prop ? $this->data[$prop] : $this->data;        
    }
    
    public function addData($prop, $value){
        $this->data[$prop] = $value;
    }
    
    public function setData($array){
        $this->data = $array;        
    }   
}