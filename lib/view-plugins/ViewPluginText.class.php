<?php

class ViewPluginText extends ViewPlugin{
    public function display(){
        $data = $this->params;
        unset($data['key']);
        
        return Lang::get($this->key, $data, $this->number);
    }
}