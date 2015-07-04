<?php

class ViewPluginButton extends ViewPlugin{
	public $class = '',
			$icon = '',
			$label = '',
			$param = array();

	public function __construct($params = array()){
		if(isset($params['data'])){
			$params = $params['data'];
		}
		parent::__construct($params);
	}
	
	public function display(){
		return View::make(ThemeManager::getSelected()->getView('button.tpl'), array(
			'class' => $this->class,
			'icon' => $this->icon,
			'label' => $this->label,
			'param' => $this->params
		));
	}	
}
