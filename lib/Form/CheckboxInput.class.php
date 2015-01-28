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
class CheckboxInput extends Input{
	const TYPE = "checkbox";
	public function __toString(){	
		if($this->value)
			$this->custom = array("checked" => "true");	
	    return parent::__toString();
	}
	
	public function dbvalue(){	
        if(!$this->dataType)
            $this->dataType = "bool";
        
		$val = $this->value === null ? false : true;
		switch($this->dataType){
			case "bool" :
			case "boolean" :
				return $val;
			case "int" :
				return (int) $val;			
		}        
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/