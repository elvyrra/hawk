<?php
/**
 * EmailInput.class.php
 * @author Elyrra SAS
 */


class EmailInput extends TextInput{

	/**
	 * Constructor
	 * @param 
	 */
	public function __construct($param){
		parent::__construct($param);
		$this->pattern = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$/";
	}
	
	public function check(&$form = null){		
		if(parent::check($form)){			
			if(!empty($this->compare) && $form){			
				if(	($form->getData($this->compare) != $this->value)) {
					$form->errors[$this->errorAt] = Lang::get("form.email-comparison");
					return false;
				}
			}			
			return true;
		}
		else
			return false;
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/