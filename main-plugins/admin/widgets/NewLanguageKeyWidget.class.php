<?php

class NewLanguageKeyWidget extends Widget{

    public function __construct(){}

	public function display(){
        $form = LanguageController::getInstance()->keyForm();
        
		return View::make(ThemeManager::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('language.key-form-add-title'),
            'icon' => 'font',
            'content' => $form
        ));        
	}
}