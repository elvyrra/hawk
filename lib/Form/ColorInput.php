<?php
/**
 * ColorInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes color inputs
 *
 * @package Form\Input
 */
class ColorInput extends FormInput{
    const TYPE = "color";

    /**
     * Display the color input
     *
     * @return string the displayed HTML
     */
    public function display(){
        $this->type = "text";

        return parent::display();
    }
}