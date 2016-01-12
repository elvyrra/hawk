<?php
/**
 * DeleteInput.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes the "delete" inputs. Delete input are submit inputs used to delete the current edited data in the database
 * @package Form\Input
 */
class DeleteInput extends ButtonInput{
	const TYPE = "submit";
	const INDEPENDANT = true;
	
	/**
	 * Display the input
	 * @return string The HTML result of the input displaying
	 */
	public function display(){		
		$this->class .= " btn-danger input-delete ";
		$this->icon = "times";
		$this->type = "submit";
		
		return parent::display();
	}
}
