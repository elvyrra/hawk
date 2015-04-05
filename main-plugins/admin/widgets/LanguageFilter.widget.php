<?php

class LanguageFilterWidget extends Widget{
	public function __construct($filters){
		$languages = array();
		foreach(Language::getAll() as $language){
			$languages[$language->tag] = $language->label;
		}	
		
		$param = array(
			'id' => 'language-filter-form',
			'method' => 'get',
			'action' => Router::getUri('LanguageController.listKeys'),
			'fieldsets' => array(
				'filters' => array(
					'nofieldset' => true,

					new SelectInput(array(
						'name' => 'tag',
						'options'  => $languages,
						'default' => $filters['tag'],
						'label' => Lang::get('language.filter-language-label'),
						'after' => '<span class="fa fa-pencil text-primary edit-lang" title="'. Lang::get('language.filter-language-edit') . '"></span>
									<span class="fa fa-close text-danger delete-lang" title="'. Lang::get('language.filter-language-delete') . '"></span>'									
					)),
					
					new RadioInput(array(
						'name' => 'keys',
						'options' => array('missing' => Lang::get('language.filter-keys-missing'), 'all' => Lang::get('language.filter-keys-all')),
						'default' => isset($filters['keys']) ? $filters['keys'] : 'all',
						'label' => Lang::get('language.filter-keys-label'),
						'labelWidth' => '100%',
						'layout' => 'vertical',
					))
				),
			)
		);
		
		$this->form = new Form($param);
	}
	
	
	public function display(){
		return View::make(ThemeManager::getSelected()->getView("box.tpl"), array(
			'title' => Lang::get('language.filter-filters-legend'),
			'icon' => 'filter',
			'content' => $this->form
		));		
	}
}