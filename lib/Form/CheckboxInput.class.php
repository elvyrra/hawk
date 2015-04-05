<?php 
/**********************************************************************
 *    						CheckboxInput.class.php
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
class CheckboxInput extends FormInput{
	const TYPE = "checkbox";
	public function __toString(){	
		if($this->value){
			$this->checked = true;
		}
	    return parent::__toString();
	}
	
	public function dbvalue(){	
        return isset($_POST[$this->name]) ? 1 : 0;		
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/