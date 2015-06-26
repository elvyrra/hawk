<?php

class ViewPluginAccordion extends ViewPlugin{
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}

		foreach($this->panels as $name => &$panel){
			if(!$panel['id']){
				$panel['id'] = uniqid();
			}

			if(!isset($this->selected)){
				$this->selected = $name;
			}
		}

		return View::make(ThemeManager::getSelected()->getView('accordion.tpl'), array(
			'id' => $this->id,
			'panels' => $this->panels
		));
	}
}