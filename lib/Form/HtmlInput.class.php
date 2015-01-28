<?php
/**********************************************************************
 *    						HtmlInput.class.php
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
class HtmlInput extends Input{
	const TYPE = 'html';
	const INDEPENDANT = true;
	const NO_LABEL = true;
	
	public function check(&$form = null){
		return true;
	}
	
	public function dbvalue($data,$format){ return;}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/