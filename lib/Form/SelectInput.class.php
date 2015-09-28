<?php
/**
 * SelectInput.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class describes the behavior of select inputs
 * @package Form\Input
 */
class SelectInput extends FormInput{
	const TYPE = 'select';	

    /**
     * The value considered as the empty one (default '')
     * @var string
     */
    public 	$emptyValue = '',

	/**
     * If set, the select will display a first option, with this property as label, and the $emptyValue as option value
     * @var string
     */
    $invitation = null,

    /**
     * The 'size' attribute
     * @var int
     */
	$size = 0,

    /**
     * The 'multiple' attribute
     * @var boolean
     */
	$multiple = false;

}