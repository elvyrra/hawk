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

    /**
     * Check the value of the input. This method is overrides the one in FormInput class,
     * and returns always <b>true</b> because no data can be submitted for this type of input
     *
     * @param Form $form The for this input is associated with
     *
     * @return boolean True
     */
    public function check(&$form = null){
        return true;
    }
}
