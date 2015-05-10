<?php

class ViewPluginAccordion extends ViewPlugin{
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}

		foreach($this->panels as &$panel){
			if(!$panel['id']){
				$panel['id'] = uniqid();
			}
		}

		return View::make(ThemeManager::getSelected()->getView('accordion.tpl'), array(
			'id' => $this->id,
			'panels' => $this->panels
		));
	}
}