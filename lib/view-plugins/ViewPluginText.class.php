<?php
/**
 * ViewPluginText.class.php
 * @author Elvyrra SAS
 */

/**
 * This class is used in view to display a language key
 * @package View\Plugins
 */
class ViewPluginText extends ViewPlugin{
    /**
     * The language key
     * @var string
     */
    public $key,

    /**
     * The key index, used if the key is an array of keys, defined for singular and plural
     */
    $number;

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