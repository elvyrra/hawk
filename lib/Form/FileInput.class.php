<?php
/**
 * FileInput.class.php
 * @author Elvyrra SAS
 */

/**
 * This class describes the file inputs behavior
 * @package Form\Input
 */
class FileInput extends FormInput{	
	const TYPE = "file";

	// This type of file is independant, no data is get or set in the database
	const INDEPENDANT = true;

	/**
	 * Defines the 'multiple' attribute of the input
	 * @var boolean
	 */
	public 	$multiple = false;

	/**
	 * Defines which extensions are allowed to be uploaded with this input
	 * @var array
	 */
	public $extensions = array();
	

	/**
	 * Display the file input
	 * @return string The displayed HTML 
	 */
	public function __toString(){
		$this->value = '';
		return parent::__toString();
	}

	/**
	 * Check the submitted value of the input
	 * @param Form $form The form the input is associated with
	 * @return boolean true if the uploaded files are correct, else false
	 */
	public function check(&$form = null){
		if(empty($this->errorAt)){
			$this->errorAt = $this->name;
		}
		
		$basename = preg_replace("/^(\w+)(\[.*)?$/", "$1", $this->name);
		$upload = Upload::getInstance($basename);

		if($this->required && !$upload){
			// No file were uploaded
			$form->error($this->errorAt, Lang::get('form.required-field'));
			return false;
		}

		if($upload && $this->extensions){			
			foreach($upload->getFiles() as $file){				
				if(!in_array($file->extension, $this->extensions)){
					// One of the uploaded files has no good extension
					$form && $form->error($this->errorAt, Lang::get('form.invalid-file-extension'));
					return false;
				}
			}
		}
		return true;		
	}
}
