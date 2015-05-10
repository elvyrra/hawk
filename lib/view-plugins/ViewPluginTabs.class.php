<?php

class ViewPluginTabs extends ViewPlugin{
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}
		foreach($this->tabs as &$tab){
			if(!$tab['id']){
				$tab['id'] = uniqid();
			}
		}
		if(!$this->selected){
			$this->selected = 1;
		}

		return View::make(ThemeManager::getSelected()->getView('tabs.tpl'), array(
			'id' => $this->id,
			'tabs' => $this->tabs,
			'selected' => $this->selected
		));

	}
}