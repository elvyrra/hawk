<?php

class ViewPluginButton extends ViewPlugin{
	public function display(){
		return View::make(ThemeManager::getSelected()->getView('button.tpl'), array(
			'class' => $this->class,
			'icon' => $this->icon,
			'label' => $this->label,
			'param' => $this->params
		));
	}	
}
