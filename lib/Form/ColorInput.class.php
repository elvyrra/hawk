<?php 
/**
 * ColorInput.class.php
 * @author Elvyrra SAS
 */



/**
 * This class describes color inputs
 */
class ColorInput extends FormInput{	
	const TYPE = "color";
	
    /**
     * Display the color input
     */
	public function __toString(){
		$this->type = "text";

		return parent::__toString();
	}
}