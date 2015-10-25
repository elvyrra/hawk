<?php
/**
 * TimeInput.php
 *	@author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes the behavior for inputs time
 * @package Form\Input
 */
class TimeInput extends FormInput{
    const TYPE = "time";
    
    /**
     * Constructor
     * @param array $param The input parameters
     */
    public function __construct($param){
		parent::__construct($param);
		$this->pattern = "/^\d{2}\:\d{2}$/";
	}
}
