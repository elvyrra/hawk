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
class DatetimeInput extends TextInput {
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
    $dbformat = 'Y-m-d',

    /**
     * The datepicker options
     */
    $picker = array(
        'orientation' => 'right',
        'todayBtn' => 'linked'
    ),

    /**
     * Display an interval ?
     */
    $interval = false;

    const TYPE = 'date';

    const INTERVAL_SEPARATOR = ' - ';

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
            elseif($this->value == '') {
                $this->value = date($this->format, time());
            }
            else {
                $this->value = date($this->format, strtotime($this->value));
            }
        }

        $this->pattern = Lang::get('main.date-moment-pattern');

        $this->class .= ' datetime';

        /*** Format the value ***/
        $this->picker['format'] = Lang::get('main.date-mask');

        if($this->max) {
            $this->picker['endDate'] = $this->max;
        }
        if($this->min) {
            $this->picker['startDate'] = $this->min;
        }
        if($this->interval) {
            $this->picker['multidate'] = 2;
            $this->picker['multidateSeparator'] = self::INTERVAL_SEPARATOR;
        }
        else {
            $this->picker['autoclose'] = true;
        }

        return parent::display();
    }


    /**
     * Check the format and the validity of a date
     * @param   string $date  The date to check
     * @param   Form   &$form The form to apply the potential errors
     * @return  bool          The check status
     */
    private function checkDate($date, &$form) {
        $tmp = date_parse_from_format($this->format, trim($date));

        if(empty($tmp)) {
            $form->error($this->errorAt, Lang::get('form.date-format'));
            return false;
        }
        // Check the date is valid
        if(!checkdate($tmp['month'], $tmp['day'], $tmp['year'])) {
            $form->error($this->errorAt, Lang::get('form.invalid-date'));
            return false;
        }

        return true;
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

        if($this->value != '') {
            if($this->interval) {
                $dates = explode(self::INTERVAL_SEPARATOR, $this->value);

                foreach($dates as $date) {
                    if(! $this->checkDate($date, $form)) {
                        return false;
                    }
                }
            }
            else {
                return $this->checkDate($this->value, $form);
            }
        }

        return true;
    }


    /**
     * Method that convert a sent value to the format for the datavase
     * @param  string $dateStr The date to format
     * @return mixed           The formatted value
     */
    private function formatDateToDb($dateStr) {
        $date = \DateTime::createFromFormat($this->format, $dateStr);

        if($this->dataType == 'int') {
            return $date->getTimestamp();
        }
        elseif($this->value == '') {
            return '';
        }
        else {
            return $date->format($this->dbformat);
        }
    }

    /**
     * Return the input value in the database format
     *
     * @return string The formatted value
     */
    public function dbvalue() {
        if($this->interval) {
            $dates = explode(self::INTERVAL_SEPARATOR, $this->value);

            return array_map(function($date) {
                return $this->formatDateToDb($date);
            }, $dates);
        }

        return $this->formatDateToDb($this->value);
    }
}
