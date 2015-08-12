<?php
/**
 * PasswordInput.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

/**
 * This class describes the behavior of password inputs
 * @package Form\Input
 */
class PasswordInput extends FormInput{
	const TYPE = "password";
	
	/**
	 * Get the value to display
	 * @var bool
	 */
	public 	$get = false,

	/**
	 * The decryption function
	 * @var callable
	 */
	$decrypt = null,
	
	/**
	 * The encryption function
	 * @var callable
	 */
	$encrypt = null,
	
	/**
	 * The input pattern
	 */
	$pattern = '/^(?=.*\d)(?=.*[a-zA-Z]).{6,16}$/';

	/**
	 * Display the input
	 * @return string The HTML result to display
	 */
	public function __toString(){
		$decrypt = $this->decrypt;
		$this->value = ($this->get && $decrypt && is_callable($decrypt)) ? $decrypt($this->value) : "";    
	    return parent::__toString();
	}
	
	/**
	 * Check the submitted value
	 * @param Form $form The form this input is associated to
	 * @return bool True if the input format is correct, else False
	 */
	public function check(&$form = null){
		if(parent::check($form)){
			// Check the confirmation password
			if(!empty($this->compare) && $form){			
				if(	$this->value != $form->getData($this->compare)){
					$form->error($this->errorAt, Lang::get('form.password-comparison'));
					return false;
				}
			}
			
			return true;
		}
		else{
			return false;
		}
	}
	

	/**
	 * Get the input value, formatted for SQL database
	 * @param string The input value, formatted for SQL database
	 */
	public function dbvalue(){		
		if($this->encrypt && is_callable($this->encrypt)){
			return call_user_func($this->encrypt, $this->value);
		}
		else
		    return $this->value;
	}
	
}
