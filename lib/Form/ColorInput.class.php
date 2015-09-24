<?php 
/**
 * ColorInput.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes color inputs
 * @package Form\Input
 */
class ColorInput extends FormInput{	
	const TYPE = "color";
	
    /**
     * Display the color input
     * @return string the displayed HTML 
     */
	public function __toString(){
		$this->type = "text";

		return parent::__toString();
	}
}