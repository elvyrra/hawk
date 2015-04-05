<?php

class FormFieldset{
	public $name, $legend, $inputs, $form;

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

	public function __toString(){
		return View::make(Plugin::get('main')->getView(Form::VIEWS_DIR . 'form-fieldset.tpl'), array(
			'fieldset' => $this,
		));
	}
}