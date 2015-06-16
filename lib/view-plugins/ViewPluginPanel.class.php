<?php

class ViewPluginPanel extends ViewPlugin{
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}

		return View::make(ThemeManager::getSelected()->getView('panel.tpl'), array(
			'id' => $this->id,
			'title' => $this->title,
			'icon' => $this->icon,
			'content' => $this->content,
			'type' => $this->type ? $this->type : 'default'	
		));
	}
}