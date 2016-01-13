<?php 
/**
 * CheckboxInput.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class describes checkboxes
 * @package Form\Input
 */
class CheckboxInput extends FormInput{

	const TYPE = "checkbox";
	
	/**
	 * display the checkbox
	 * @return string The displayed HTML
	 */
	public function display(){	
		if($this->value){
			$this->checked = true;
		}
	    return parent::display();
	}
	

	/**
	 * Return the value of the input, formatted for MySQL database 
	 * @return integer - if the checkbox has been submitted as checked, returns 1, else return 0
	 */
	public function dbvalue(){	
        return App::request()->getBody($this->name) ? '1' : '0';		
	}
}
