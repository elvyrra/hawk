<?php

class TimeInput extends FormInput{
    const TYPE = "time";
    
    public function __construct($param){
		parent::__construct($param);
		$this->pattern = "/^\d{2}\:\d{2}$/";
	}
}
