<?php

class ViewPluginText extends ViewPlugin{
    public function display(){
        $data = $this->params;
        unset($data['key']);
        
        if(empty($this->number)){
        	return Lang::get($this->key, $data);	
        }
        else{
        	return Lang::get($this->key, $data, $this->number);
        }
    }
}