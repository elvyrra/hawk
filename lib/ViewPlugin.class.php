<?php


abstract class ViewPlugin{
    public function __construct($params = array()){
        $this->params = $params;
        foreach($params as $key => $value){
            $this->$key = $value;
        }
    }
    
    abstract public function display();
}