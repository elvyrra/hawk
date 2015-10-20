<?php

namespace Hawk\Plugins\Admin;

class LanguageFilterWidget extends Widget{
	public function __construct($filters){
		$options = array();
		// active languages
		$languages = array(); 
		foreach(Language::getAll() as $language){
			$options[$language->tag] = $language->label;
			if($language->active){
				$languages[$language->tag] = $language;
			}
		}	


		
		$param = array(
			'id' => 'language-filter-form',
			'method' => 'get',
			'action' => Router::getUri('language-keys-list'),
			'fieldsets' => array(
				'filters' => array(
					'nofieldset' => true,

					new SelectInput(array(
						'name' => 'tag',
						'options'  => $options,
						'default' => $filters['tag'],
						'style' => 'width: 80%; margin-right: 5px;',
						'label' => Lang::get('language.filter-language-label'),
						'after' => '<span class="icon icon-pencil text-primary edit-lang" title="'. Lang::get('language.filter-language-edit') . '"></span>' . 
									(count($languages) > 1 && Option::get('main.language') != $filters['tag'] && $filters['tag'] != Lang::DEFAULT_LANGUAGE ? '<span class="icon icon-close text-danger delete-lang" title="'. Lang::get('language.filter-language-delete') . '"></span>' : '')
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
		return View::make(Theme::getSelected()->getView("box.tpl"), array(
			'title' => Lang::get('language.filter-filters-legend'),
			'icon' => 'filter',
			'content' => $this->form
		));		
	}
}