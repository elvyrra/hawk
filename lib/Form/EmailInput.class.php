<?php
/**
 * EmailInput.class.php
 * @author Elyrra SAS
 */

namespace Hawk;

/**
 * This class describes email inputs behavior
 * @package Form\Input
 */
class EmailInput extends TextInput{

	/**
	 * Constructor
	 * @param array $param The input parameters
	 */
	public function __construct($param){
		parent::__construct($param);
		$this->pattern = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$/";
	}
	

	/**
	 * Check the format of the submitted value
	 * @param Form $form The form the input is associated with
	 * @return boolean true if the submitted value is a correct email, else false
	 */
	public function check(&$form = null){		
		if(parent::check($form)){			
			if(!empty($this->compare) && $form){			
				if(	($form->getData($this->compare) != $this->value)) {
					$form->error($this->errorAt, Lang::get("form.email-comparison"));
					return false;
				}
			}			
			return true;
		}
		else
			return false;
	}
}
