<?php
/**********************************************************************
 *    						IntegerInput.class.php
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
class IntegerInput extends NumberInput{	
	public function check(&$form = null){				
		if(parent::check($form)){
			if(!empty($this->value) && !preg_match('/^\d*$/',$this->value)){
				$form->errors[$this->errorAt] = Lang::get('form.integer-format');
				return false;
			}			
			return true;
		}	
		else
			return false;
	}
	
	public function dbvalue(){
	    return (int)($this->value);   
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/