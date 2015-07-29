<?php
/**********************************************************************
 *    						NumberInput.class.php
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
class NumberInput extends FormInput{
	const TYPE = "number";

	public function check(&$form = null){
		if(parent::check($form)){
			if(!empty($this->value) && !is_numeric($this->value)){
				$form->error($this->errorAt, Lang::get('form.number-format'));
				return false;
			}
			elseif(isset($this->minimum) && $this->value < $this->minimum){
				$form->error($this->errorAt, Lang::get("form.number-minimum", array('value' => $this->minimum)));
				return false;		
			}
			elseif(isset($this->maximum) && $this->value > $this->maximum){
				$form->error($this->errorAt, Lang::get("form.number-maximum", array('value' => $this->maximum)));
				return false;			
			}
			return true;
		}	
		else
			return false;
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/