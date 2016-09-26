<?php
/**
 * DatetimeInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes date and datetime inputs
 *
 * @package Form\Input
 */
class DatetimeInput extends TextInput{
    /**
     * The maximal date the user can fill
     */
    public $max = null,

    /**
     * The minimum date the user can fill
     */
    $min = null,

    /**
     * The format of the date
     */
    $format = null,

    /**
     * The format for database
     */
    $dbformat = 'Y-m-d';

    const TYPE = 'date';

    /**
     * Constructor
     *
     * @param array $param The parameters of the input
     */
    public function __construct($param) {
        parent::__construct($param);

        $this->type = 'text';

        if(empty($this->format)) {
            $this->format = Lang::get('main.date-format');
        }

    }

    /**
     * Display the input
     *
     * @return string The HTML result of displaying
     */
    public function display() {
        if(is_numeric($this->value)) {
            $this->value = $this->value ? date($this->format, $this->value) : '';
        }
        else {
            if($this->value === '0000-00-00' || $this->value === '0000-00-00 00:00:00') {
                $this->value = '';
            }
            else if($this->value == ''){
                $this->value = date($this->format, time());
            }
            else {
                $this->value = date($this->format, strtotime($this->value));
            }
        }

        $this->pattern = Lang::get('main.date-moment-pattern');

        $this->class .= ' datetime';

        /*** Format the value ***/
        $picker = array(
            'format' => Lang::get('main.date-mask'),
            'orientation' => 'right'
        );

        if($this->max) {
            $picker['endDate'] = $this->max;
        }
        if($this->min) {
            $picker['startDate'] = $this->min;
        }

        $this->picker = json_encode($picker);

        return parent::display();
    }


    /**
     * Check the submitted value
     *
     * @param Form $form The form to apply the errors on in case of check failure
     *
     * @return bool the check status
     */
    public function check(&$form = null){
        // First check the global input validators
        if(! parent::check($form)) {
            return false;
        }

        if($this->value!="") {
            // Check the format of the given date
            $tmp = date_parse_from_format($this->format, $this->value);

            if(empty($tmp)) {
                $form->error($this->errorAt, Lang::get('form.date-format'));
                return false;
            }
            // Check the date is valid
            if(!checkdate($tmp['month'], $tmp['day'], $tmp['year'])) {
                $form->error($this->errorAt, Lang::get('form.invalid-date'));
                return false;
            }

        }
        return true;
    }


    /**
     * Return the input value in the database format
     *
     * @return string The formatted value
     */
    public function dbvalue(){
        $date = \DateTime::createFromFormat($this->format, $this->value);

        if($this->dataType == 'int') {
            return $date->getTimestamp();
        }
        else if($this->value == ""){
            return '';
        }
        else{
            return $date->format($this->dbformat);
        }
    }
}
