<?php

class NewLanguageKeyWidget extends Widget{
	public function __construct(){

	}

	public function display(){
		$form = (new LanguageController())->keyForm(0);

		return View::make(ThemeManager::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('language.key-form-add-title'),
            'icon' => 'font',
            'content' => $form
        ));        
	}
}