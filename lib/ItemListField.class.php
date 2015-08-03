<?php
/**
 * ItemListField.class.php
 */



/**
 * This class describes the field displayed in a smart list
 */
class ItemListField{

	public  $field = null,
			$class = null,
			$title = null, 
			$href = null,
			$onclick = null, 
			$style = null, 
			$unit = null,
			$display = null, 
			$target = null,
			$sort = true,
			$search = true,
			$independant = false,
			$label = null,
			$hidden = false;

	/**
	 * Constructor
	 * @param array $param The field parameters
	 */
	public function __construct($param){
		foreach($param as $key => $value){
			$this->$key = $value;
		}
	}


	/**
	 * Get the displayed value
	 */
	
}