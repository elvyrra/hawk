<?php
/**********************************************************************
 *    						DateTimeInput.class.php
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
class DateTimeInput extends TextInput{	
	public function __toString(){
		if(!isset($this->format))
			$this->format = 'Y-m-d';
		
		if(is_numeric($this->value))
			$this->timestamp = $this->value;
		else
			$this->timestamp = strtotime($this->value);
					
		$this->value = date($this->format,$this->timestamp);
		$this->class .= " datetime";
		
		/*** Format the value ***/					
		return parent::__toString().($this->mask ? "<script>$('#$this->id').mask('$this->mask');</script>" : "");		
	}
	
	public function check(&$form = null){
		if(! parent::check($form))
			return false;
		
		if($this->value!=""){				
			// Check the format of the given date
			$tmp = date_parse_from_format($this->format, $this->value);
			if(empty($tmp)){
				$form->errors[$this->errorAt] = Lang::get('form.date-format');
				return false;
			}		
			// Check the date is valid
			if(!checkdate($tmp['month'], $tmp['day'], $tmp['year'])){					
				$form->errors[$this->errorAt] = Lang::get('form.invalid-date');
				return false;
			}						
			
		}			
		return true;
	}
	
	public function dbvalue(){
		$date = DateTime::createFromFormat($this->format, $this->value);
		if($this->dataType == 'int')
			return $date->getTimestamp();
		else
			return $date->format($this->dbformat);		
	}	
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/