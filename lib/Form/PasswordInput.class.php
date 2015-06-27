<?php
/**********************************************************************
 *    						PasswordInput.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
class PasswordInput extends FormInput{
	const TYPE = "password";
	

	public 	$get = false,
			$decrypt = false,
			$encrypt = false,
			$check = false;

	public function __construct($param){
		parent::__construct($param);
		$this->pattern = "/^(?=.*\d)(?=.*[a-zA-Z]).{6,16}$/";
	}
	
	public function __toString(){
		$decrypt = $this->decrypt;
		$this->value = ($this->get && $decrypt && is_callable($decrypt)) ? $decrypt($this->value) : "";    
	    return parent::__toString();
	}
	
	public function check(&$form = null){
		if(parent::check($form)){
			// Check the confirmation password
			if(!empty($this->compare) && $form){			
				if(	$this->value != $form->getData($this->compare)){
					$form->errors[$this->errorAt] = Lang::get('form.password-comparison');
					return false;
				}
			}
			// Check the password value in the database
			elseif(!empty($this->value) && $this->check && $form){
			    $checkField = is_boolean($this->check) ? $this->field : $this->check;
				
				$example = new DBExample(array(
					$form->reference,
					$checkField => $this->dbvalue()
				));
				$model = $form->model;
				if($model::getByExample($example)){
					$form->errors[$this->errorAt] = Lang::get('form.invalid-password');
					return false;
				}		
			}
			return true;
		}
		else{
			return false;
		}
	}
	
	public function dbvalue(){		
		if($this->encrypt && is_callable($this->encrypt)){
			return call_user_func($this->encrypt, $this->value);
		}
		else
		    return $this->value;
	}
	
	
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/