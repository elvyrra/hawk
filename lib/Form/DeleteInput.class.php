<?php
/**
 * DeleteInput.class.php
 * @author Elvyrra SAS
 */

/**
 * This class describes the "delete" inputs. Delete input are submit inputs used to delete the current edited data in the database
 */
class DeleteInput extends ButtonInput{
	const TYPE = "submit";
	const INDEPENDANT = true;
	const NO_LABEL = true;
	
	/**
	 * Display the input
	 * @return string The HTML result of the input displaying
	 */
	public function __toString(){		
		$this->class .= " btn-danger input-delete ";
		$this->icon = "times";
		$this->type = "submit";
		
		return parent::__toString();
	}
}
