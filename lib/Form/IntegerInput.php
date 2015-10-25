<?php
/**
 * IntegerInput.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes the behavior of inputs that value must be an integer
 * @package Form\Input
 */
class IntegerInput extends NumberInput{
	/**
	 * The input pattern
	 * @var string
	 */
	public $pattern = '/^[\-]?\d*$/';

	/**
	 * Return the input value, formatted for the SQL database
	 * @return int The formatted value
	 */
	public function dbvalue(){
	    return (int)($this->value);   
	}
}
