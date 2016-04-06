<?php
/**
 * RadioInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the input[type='radio']
 *
 * @package Form\Input
 */
class RadioInput extends FormInput{
    const TYPE = "radio";

    /**
     * The layout of the inputs : 'horizontal' (default), 'vertical'
     *
     * @var string
     */
    public $layout = 'horizontal',

    /**
     * The radio button options
     */
    $options = array();

}
