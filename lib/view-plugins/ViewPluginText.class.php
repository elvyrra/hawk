<?php
/**
 * ViewPluginText.class.php
 */



/**
 * This class is used in view to display a language key
 */
class ViewPluginText extends ViewPlugin{
    /**
     * Display the language key
     */
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