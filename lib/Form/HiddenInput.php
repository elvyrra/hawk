<?php
/**
 * HiddenInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes hidden inputs behavior
 *
 * @package Form\Input
 */
class HiddenInput extends FormInput{
    const TYPE = "hidden";

    /**
     * Is the input hidden
     */
    public $hidden = true;
}
