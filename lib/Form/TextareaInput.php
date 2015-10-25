<?php
/**
 * TextareaInput.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

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
	public 	$rows = 15, 

	/**
	 * The attribute 'cols'
	 * @var int
	 */
	$cols = 30;
}
