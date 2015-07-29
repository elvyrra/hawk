<?php
/**********************************************************************
 *    						FloatInput.class.php
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
class FloatInput extends NumberInput{
	public function __construct($param){
		$this->decimals = 2;
		$this->pattern = "/^[0-9]+(.[0-9])?/";
		
		parent::__construct($param);	
	}
	
	public function __toString(){
		$this->value = number_format(floatval($this->value), $this->decimals, ".", "");
		return parent::__toString();
	}
	
	public function check(&$form = null){
		if(parent::check($form)){
			if(!empty($this->value) && !is_numeric($this->value)){
				$form->error($this->errorAt, Lang::get('form.number-format'));
				return false;
			}			
			return true;
		}	
		else
			return false;
	}
	
	public function dbvalue(){
	    return (float)($this->value);   
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/