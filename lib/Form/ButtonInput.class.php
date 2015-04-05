<?php
/**********************************************************************
 *    						ButtonInput.class.php
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
class ButtonInput extends FormInput{
	const TYPE = "button";
	const INDEPENDANT = true;
	const NO_LABEL = true;
	
	private static $defaultIcons = array(
		'valid' => 'save',
		'save' => 'save',
		'cancel' => 'ban',
		'close' => 'times',
		'delete' => 'times',
		'back' => 'reply',
		'next' => 'step-forward',
		'previous' => 'step-backward',
		'send' => 'mail-closed'	
	);
	
	public function __toString(){
		if(!empty($this->notDisplayed)){
			return '';
		}
		
		$param = get_object_vars($this);
		$param["class"] .= " form-button";
		if(empty($param['icon']) && isset(self::$defaultIcons[$this->name]))
			$param['icon'] = self::$defaultIcons[$this->name];
		
		$param = array_filter($param, function($v){ return !empty($v);});

		$param['label'] = $this->value;
		$param['type'] = static::TYPE;
		
		$param = array_intersect_key($param, array_flip(array('id', 'class', 'icon', 'label', 'type', 'name', 'onclick', 'style', 'href', 'target')));
		
		/*** Set the attributes of the button ***/	
		if(!preg_match("!\bbtn-\w+\b!", $param['class']))
			$class .= " btn-inverse";
		
		/*** Set the attribute and text to the span inside the button ***/
		$param = array_map(function($v){return htmlentities($v, ENT_QUOTES); }, $param);
		
		return View::make(ThemeManager::getSelected()->getView('button.tpl') ,array(
			'class' => isset($param['class']) ? $param['class'] : '',
			'param' => $param,
			'icon' => isset($param['icon']) ? $param['icon'] : '',
			'label' => isset($param['label']) ? $param['label'] : '',
			'textStyle' => isset($param['textStyle']) ? $param['textStyle'] : '',
		));		
	}
	
	public function check(&$form = null){
		return true;
	}	
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/