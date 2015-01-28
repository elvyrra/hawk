<?php
/**********************************************************************
 *    						EmailInput.class.php
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
class EmailInput extends TextInput{
	public function __construct($param){
		parent::__construct($param);
		$this->pattern = "^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$";
	}
	
	public function check(&$form = null){		
		if(parent::check($form)){			
			if(!empty($this->compare) && $form){			
				if(	($form->data[$this->compare] != $this->value)) {
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