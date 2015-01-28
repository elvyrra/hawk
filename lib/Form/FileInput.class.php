<?php
/**********************************************************************
 *    						FileInput.class.php
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
class FileInput extends Input{	
	const TYPE = "file";
	const INDEPENDANT = true;
	
	public function check(&$form = null){
		if(empty($this->errorAt))
			$this->errorAt = $this->name;
			
		if($this->required && empty($_FILES[$this->name])){
			$form->errors[$this->errorAt] = Lang::get('form.required-field');
			return false;
		}
		return true;		
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/