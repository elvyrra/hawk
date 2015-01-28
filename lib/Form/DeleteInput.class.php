<?php
/**********************************************************************
 *    						DeleteInput.class.php
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
class DeleteInput extends ButtonInput{
	const TYPE = "submit";
	const INDEPENDANT = true;
	const NO_LABEL = true;
	
	public function __toString(){		
		$this->class .= " btn-danger input-delete ";
		$this->icon = "times";
		$this->type = "submit";
		
		return parent::__toString();
	}
	
	public function check(&$form = null){
		return true;
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/