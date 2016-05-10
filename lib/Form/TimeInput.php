<?php
/**
 * TimeInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the behavior for inputs time
 *
 * @package Form\Input
 */
class TimeInput extends FormInput{
    const TYPE = "time";

    const FORMAT = 'H:i';

    public $mask = '99:99',

    $pattern = '/^\d{2}\:\d{2}$/';

    /**
     * Display the input
     *
     * @return string The HTML result of displaying
     */
    public function display(){

        if(is_numeric($this->value)) {
            $this->timestamp = $this->value;
        }
        else{
            $this->timestamp = strtotime($this->value);
        }

        $this->value = $this->timestamp ? date(self::FORMAT, $this->timestamp) : '';

        return parent::display();
    }

    /**
     * Return the input value in the database format
     *
     * @return string The formatted value
     */
    public function dbvalue(){
        if($this->dataType == 'int') {
            list($hours, $minutes) = explode(':', $this->value);

            return $hours * 3600 + $minutes * 60;
        }
        else{
            return $this->value;
        }
    }
}
