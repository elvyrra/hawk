<?php
/**
 * FloatInput.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes input that must contain float values
 * @package Form\Input
 */
class FloatInput extends NumberInput{

	/**
	 * Constructor
	 * @param array $param The input parameters
	 */
	public function __construct($param){
		$this->decimals = 2;
		$this->pattern = "/^[0-9]+(.[0-9])?/";
		
		parent::__construct($param);	
	}
	

	/**
	 * Display the input
	 * @return string The displayed HTML
	 */
	public function __toString(){
		$this->value = number_format(floatval($this->value), $this->decimals, ".", "");
		return parent::__toString();
	}
	

	/**
	 * Check the format of the submitted value
	 * @param Form $form The form the input is associated with
	 * @return boolean true if the submitted value has the right syntax, else false
	 */
	public function check(&$form = null){
		if(parent::check($form)){
			if(!empty($this->value) && !is_numeric($this->value)){
				// The value is not numeric
				$form->error($this->errorAt, Lang::get('form.number-format'));
				return false;
			}			
			return true;
		}	
		else{
			return false;
		}
	}
	
	/**
	 * Get the value, formatted for the MySQL database
	 * @return float The formatted value
	 */
	public function dbvalue(){
	    return (float)($this->value);   
	}
}
