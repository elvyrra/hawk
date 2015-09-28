<?php
/**
 * ButtonInput.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class describes the button inputs in form (button, delete, dans submit)
 * @package Form\Input
 */
class ButtonInput extends FormInput{
	// the type of the input
	const TYPE = "button";
	// this type of input is independant, so not inserted in database
	const INDEPENDANT = true;

	/**
	 * Defines the icons for most common buttons
	 * @static array $defaultIcons
	 */
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

	/**
	 * Defines if the input has to be displayed on a new line. For button inputs, this property is defaulty set to false
	 * @var boolean
	 */
	public $nl = false;
	
	/**
	 * Display the input 
	 * @return string The dislayed HTML
	 */
	public function __toString(){
		if(!empty($this->notDisplayed)){
			return '';
		}
		
		$param = get_object_vars($this);
		$param["class"] .= " form-button";
		if(empty($param['icon']) && isset(self::$defaultIcons[$this->name]))
			$param['icon'] = self::$defaultIcons[$this->name];
		
		$param = array_filter($param, function($v){ return !empty($v);});

		if(!isset($param['label'])){
			$param['label'] = $this->value;
		}
		$param['type'] = static::TYPE;
		
		$param = array_intersect_key($param, array_flip(array('id', 'class', 'icon', 'label', 'type', 'name', 'onclick', 'style', 'href', 'target')));
		$param = array_merge($param, $this->attributes);
		
		/*** Set the attributes of the button ***/	
		if(!preg_match("!\bbtn-\w+\b!", $param['class'])){
			$param['class'] .= " btn-default";
		}
		
		/*** Set the attribute and text to the span inside the button ***/
		$param = array_map(function($v){return htmlentities($v, ENT_QUOTES); }, $param);
		
		return View::make(Theme::getSelected()->getView('button.tpl') ,array(
			'class' => isset($param['class']) ? $param['class'] : '',
			'param' => $param,
			'icon' => isset($param['icon']) ? $param['icon'] : '',
			'label' => isset($param['label']) ? $param['label'] : '',
			'textStyle' => isset($param['textStyle']) ? $param['textStyle'] : '',
		));		
	}
	

	/**
	 * Check the submitted value
	 * @param Form $form The form the input is associated with
	 * @return bool This function always return true, because no value is expected from a button
	 */
	public function check(&$form = null){
		return true;
	}	
}
