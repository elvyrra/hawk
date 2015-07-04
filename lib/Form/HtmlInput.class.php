<?php
/**
 * HtmlInput.class.php
 */
class HtmlInput extends FormInput{
	const TYPE = 'html';
	const INDEPENDANT = true;

    public $plainText = false;

	public function check(&$form = null){
		return true;
	}
	
	public function dbvalue($data,$format){ return $data;}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/