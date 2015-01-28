<?php

class AdminController extends Controller{
	public function index(){
		
	}
	
	public function settings(){		
		$options = Option::getPluginOptions('main');
		$languages = array();
		foreach(LanguageModel::getAll() as $lang){
			$languages[$lang->tag] = $lang->label;
		}
		
		$param = array(
			'id' => 'settings-form',
			'fieldsets' => array(
				'main' => array(
					'legend' => Lang::get('admin.settings-main-legend'),
					
					new TextInput(array(
						'name' => 'title',
						'required' => true,
						'default' => $options['title'],
						'label' => Lang::get('admin.settings-title-label')
					)),
					
					new SelectInput(array(
						'name' => 'language',
						'required' => true,
						'options' => $languages,
						'default' => $options['language'],
						'label' => Lang::get('admin.settings-language-label'),
					)),
					
					new SelectInput(array(
						'name' => 'timezone',
						'required' => true,
						'options' => array_combine(DateTimeZone::listIdentifiers(), DateTimeZone::listIdentifiers()),
						'default' => $options['timezone'],
						'label' => Lang::get('admin.settings-timezone-label')
					))
				),
				
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'save',
						'value' => Lang::get('main.valid-button'),				
					)),
					
					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
					))
				),
			),
		);
		
		$form = new Form($param);
		
		if(!$form->submitted()){
			return View::make($this->theme->getView('tab.tpl'), array(
				'icon' => 'cogs',
				'title' => Lang::get('admin.settings-page-name'),
				'description' => Lang::get('admin.settings-page-description'),
				'content' => $form				
			));
		}
		else{
			
		}
	}
	
}