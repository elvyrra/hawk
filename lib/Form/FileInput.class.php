<?php
/**********************************************************************
 *    						FileInput.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
class FileInput extends FormInput{	
	const TYPE = "file";
	const INDEPENDANT = true;

	public function __construct($param){
		parent::__construct($param);

		$bind = "value: value";
		if(!empty($this->attributes['data-bind'])){
			$this->attributes['data-bind'] .= ', ' . $bind;
		}
		else{
			$this->attributes['data-bind'] = $bind;
		}
	}

	public 	$multiple = false;

	public $extensions = array();
	
	public function check(&$form = null){
		if(empty($this->errorAt))
			$this->errorAt = $this->name;
		
		$basename = preg_replace("/^(\w+)(\[.*)?$/", "$1", $this->name);
		$upload = Upload::getInstance($basename);
		if($this->required && !$upload){
			$form->error($this->errorAt, Lang::get('form.required-field'));
			return false;
		}

		if($upload && $this->extensions){			
			foreach($upload->getFiles() as $file){				
				if(!in_array($file->extension, $this->extensions)){
					$form && $form->error($this->errorAt, Lang::get('form.invalid-file-extension'));
					return false;
				}
			}
		}
		return true;		
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/