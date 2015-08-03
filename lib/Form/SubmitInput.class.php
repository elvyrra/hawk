<?php


class SubmitInput extends ButtonInput{
	const TYPE = "submit";
	const INDEPENDANT = true;
	const NO_LABEL = true;
	
	public function __toString(){				
		$this->class .= " btn-info";
		return parent::__toString();		
	}
	
	public function check(&$form = null){		
		return true;
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/