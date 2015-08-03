<?php 
/**
 * CheckboxInput.class.php
 * @author Elvyrra SAS
 */


/**
 * This class describes checkboxes
 */
class CheckboxInput extends FormInput{

	const TYPE = "checkbox";
	
	/**
	 * display the checkbox
	 * @return string The HTML result of displaying
	 */
	public function __toString(){	
		if($this->value){
			$this->checked = true;
		}
	    return parent::__toString();
	}
	

	/**
	 * Return the value of the input, formatted for MySQL database 
	 * @return integer - if the checkbox has been submitted as checked, returns 1, else return 0
	 */
	public function dbvalue(){	
        return isset($_POST[$this->name]) ? '1' : '0';		
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/