<?php
/**
 * HtmlInput.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes html inputs. These objects are not really inputs, but can be used in forms to display HTML content betweeen other inputs, 
 * for instance short description of a set of inputs.
 * @package Form\Input
 */
class HtmlInput extends FormInput{
	const TYPE = 'html';
	const INDEPENDANT = true;

	/**
	 * Defines if the content must be displayed as plain text
	 */
    public $plainText = false;

    /**
     * Display the value of the input
     * @return string The displayed HTML or text
     */
    public function __toString(){
    	if($this->plainText){
    		$this->value = nl2br($this->value);
    	}

    	return parent::__toString();
    }

    /**
     * Check the submitted value format. For this class, this method always return true, because no data can be submitted
     * @param Form $form The form this "input" is associated with
	 * @return boolean True
     */
	public function check(&$form = null){
		return true;
	}
	
	/**
	 * Get the value, formatted for MySQL database
	 * @return string The value itself
	 */
	public function dbvalue(){ 
		return $this->value;
	}
}
