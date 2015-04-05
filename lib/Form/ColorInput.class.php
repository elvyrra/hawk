<?php 

class ColorInput extends FormInput{	
	const TYPE = "color";
	
	public function __toString(){
		$this->type = "text";
		return parent::__toString();
	}
}