<?php
/**
 * SubmitInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the submit inputs behavior
 *
 * @package Form\Input
 */
class SubmitInput extends ButtonInput{
    const TYPE = "submit";

    const INDEPENDANT = true;

    const NO_LABEL = true;

    /**
     * Display the input
     *
     * @return string The HTML to display
     */
    public function display(){
        $this->class .= " btn-primary";
        return parent::display();
    }
}
