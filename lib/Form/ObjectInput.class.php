<?php
/**********************************************************************
 *    						ObjectInput.class.php
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
/*** This file describe the behavio of an input representing an object.
 * To be treated, an object must be an array. 
 * To be displayed, an object must be a json string
 * */
class ObjectInput extends Input{ 
	const TYPE="text";
	
    public function __toString(){
		if(empty($this->value))
			$this->value = "{}";
		elseif(is_array($this->value))
			$this->value = json_encode($this->value, JSON_NUMERIC_CHECK | JSON_HEX_APOS | JSON_HEX_QUOT);
		
        return parent::__toString();
    }   
	
	public function dbvalue(){
		//return json_decode($this->value, true);
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/