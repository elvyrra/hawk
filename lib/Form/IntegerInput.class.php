<?php
/**
 * IntegerInput.class.php
 */


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