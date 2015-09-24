<?php
/**
 * NummberInput.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes the behavio of number inputs
 * @package Form\Input
 */
class NumberInput extends FormInput{
	const TYPE = "number";

	/**
     * Check the submitted value of the input
     * @param Form &$form The form the input is associated with
     * @return bool True if the submitted value is valid, else false
     */
	public function check(&$form = null){
		// Start by checking general validators
		if(parent::check($form)){
			if(!empty($this->value) && !is_numeric($this->value)){
				// The value is not numeric
				$form->error($this->errorAt, Lang::get('form.number-format'));
				return false;
			}
			elseif(isset($this->minimum) && $this->value < $this->minimum){
				// The value is lower than the given minimum
				$form->error($this->errorAt, Lang::get("form.number-minimum", array('value' => $this->minimum)));
				return false;		
			}
			elseif(isset($this->maximum) && $this->value > $this->maximum){
				// The value is greater than the given maximum
				$form->error($this->errorAt, Lang::get("form.number-maximum", array('value' => $this->maximum)));
				return false;			
			}
			// The value is valid
			return true;
		}	
		else{
			return false;
		}
	}
}
