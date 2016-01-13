<?php
/**
 * ObjectInput.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describe the behavior of an input representing an object.
 * To be treated, an object must be an array. 
 * To be displayed, an object must be a json string
 * @package Form\Input
 */
class ObjectInput extends FormInput{ 
	const TYPE="text";
	
    /**
     * Display the input
     * @return string The HTML result to display
     */
    public function display(){
		if(empty($this->value)){
			$this->value = "{}";
		}
		elseif(is_array($this->value)){
			$this->value = json_encode($this->value, JSON_NUMERIC_CHECK | JSON_HEX_APOS | JSON_HEX_QUOT);
		}
		
        return parent::display();
    }
	
}
