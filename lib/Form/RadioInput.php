<?php
/**
 * RadioInput.php
 */

namespace Hawk;

/**
 * This class describes the input[type='radio']
 * @package Form\Input
 */
class RadioInput extends FormInput{	
	const TYPE = "radio";

	/**
	 * The layout of the inputs : 'horizontal' (default), 'vertical'
	 */
	public 	$layout = 'horizontal';

}