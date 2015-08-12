<?php
/**
 * TextareaInput.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

/**
 * This class describes the behavior of textareas
 * @package Form\Input
 */
class TextareaInput extends FormInput{
	const TYPE = 'textarea';

	/**
	 * The attribute 'rows'
	 * @var int
	 */
	public 	$rows, 

	/**
	 * The attribute 'cols'
	 * @var int
	 */
	$cols;
}
