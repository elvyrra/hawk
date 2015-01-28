<?php

class ViewPluginUri extends ViewPlugin{
    public function display(){
        unset($this->params['action']);
        return Router::getUri($this->action, $this->params);
    }
}