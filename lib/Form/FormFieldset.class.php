<?php
/**
 * FormFieldset.class.php
 * @author Elvyrra SAS
 */

/**
 * This class describes the behavior of a form fieldset
 */
class FormFieldset{
	public $name, $legend, $inputs, $form, $legendId = '';

	/**
	 * Constructor
	 */
	public function __construct($form, $name, $inputs= array(), $params = array()){
		$this->name = $name;		
		$this->form = $form;
		$this->id = $form->id . '-' . $this->name . '-fieldset';
		$this->inputs = $inputs;
		foreach($params as $key => $value){
			$this->$key = $value;
		}
		if($this->legend){
			$this->legendId = $form->id . '-' . $this->name . '-legend';
		}
	}


	public function setParam($param, $value){
		$this->$param = $value;
	}


	public function __toString(){
		return View::make(ThemeManager::getSelected()->getView(Form::VIEWS_DIR . 'form-fieldset.tpl'), array(
			'fieldset' => $this,
		));
	}
}