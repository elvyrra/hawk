<?php
/**
 * DatetimeInput.class.php
 * @author Elvyrra SAS
 */
/**
 * This class describes date and datetime inputs
 */
class DatetimeInput extends TextInput{	

	public $max = null, 
			$min = null,
			$format = null,
			$dbformat = 'Y-m-d';
	
	public function __construct($param){
		parent::__construct($param);
		
		if(empty($this->format)){
			$this->format = Lang::get('main.date-format');			
		}

	}		

	/**
	 * Display the input
	 * @return string The HTML result of displaying
	 */
	public function __toString(){
		
		if(is_numeric($this->value)){
			$this->timestamp = $this->value;
		}
		else{
			$this->timestamp = strtotime($this->value);
		}
					
		$this->value = date($this->format, $this->timestamp);
		$this->class .= " datetime";
		
		/*** Format the value ***/	
		$picker = array('format' => Lang::get('main.date-mask'));
		if($this->max){
			$picker['endDate'] = $this->max;
		}
		if($this->min){
			$picker['startDate'] = $this->min;
		}
		return parent::__toString() . "<script>$('#$this->id').datepicker(" . json_encode($picker) . ");</script>";
	}
	

	/**
	 * Check the submitted value
	 * @param Form &$form The form to apply the errors on in case of check failure
	 * @return bool the check status
	 */
	public function check(&$form = null){
		// First check the global input validators
		if(! parent::check($form)){
			return false;
		}
		
		if($this->value!=""){				
			// Check the format of the given date
			$tmp = date_parse_from_format($this->format, $this->value);
			if(empty($tmp)){
				$form->error($this->errorAt, Lang::get('form.date-format'));
				return false;
			}		
			// Check the date is valid
			if(!checkdate($tmp['month'], $tmp['day'], $tmp['year'])){					
				$form->error($this->errorAt, Lang::get('form.invalid-date'));
				return false;
			}						
			
		}			
		return true;
	}
	

	/**
	 * Return the input value in the database format
	 */
	public function dbvalue(){
		$date = DateTime::createFromFormat($this->format, $this->value);
		if($this->dataType == 'int'){
			return $date->getTimestamp();
		}
		else{
			return $date->format($this->dbformat);		
		}
	}	
}
