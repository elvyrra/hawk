<?php
/**
 * SelectInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the behavior of select inputs
 *
 * @package Form\Input
 */
class SelectInput extends FormInput{
    const TYPE = 'select';

    /**
     * The value considered as the empty one (default '')
     *
     * @var string
     */
    public $emptyValue = '',

    /**
     * If set, the select will display a first option, with this property as label, and the $emptyValue as option value
     *
     * @var string
     */
    $invitation = null,

    /**
     * The 'size' attribute
     *
     * @var int
     */
    $size = 0,

    /**
     * The 'multiple' attribute
     *
     * @var boolean
     */
    $multiple = false,

    /**
     * The selectbox options. Each element key is the option value, and the element value can be :
     *     - a String, representing the option label
     *     - an array containing the parameters 'label' and 'group' to put the option in an option group
     *
     * @var array
     */
    $options = array(),

    /**
     * The selectbox option groups. The keys represent the group name, and the values, the label that will be displayed
     * for the option group
     *
     * @var array
     */
    $optgroups = array();
}
