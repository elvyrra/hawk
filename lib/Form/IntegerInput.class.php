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
	public function __construct($param){
		parent::__construct($param);
		
		$this->pattern = "/^[\-]?\d*$/";
	}
	
	public function dbvalue(){
	    return (int)($this->value);   
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/