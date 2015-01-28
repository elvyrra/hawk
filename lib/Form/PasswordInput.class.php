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
class PasswordInput extends Input{
	const TYPE = "password";
	
	public function __construct($param){
		parent::__construct($param);
		$this->pattern = "^.{6,}$";
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
				if(	$this->value != $form->data[$this->compare]){
					$form->errors[$this->errorAt] = Lang::get('form.password-comparison');
					return false;
				}
			}
			// Check the password value in the database
			elseif(!empty($this->value) && $this->check && $form){
			    $check_field = is_boolean($this->check) ? $this->field : $this->check;
				
				if(!$form->database->count($form->table, $form->condition . " AND $check_field = :$check_field", array_merge($form->binds, array($check_field => $this->dbvalue())))){
					$form->errors[$this->errorAt] = Lang::get('form.invalid-password');
					return false;
				}		
			}
			return true;
		}
		else
			return false;
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