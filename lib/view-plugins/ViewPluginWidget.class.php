<?php

class ViewPluginWidget extends ViewPlugin{
	public function display(){
		$classname = $this->class;
		$component = new $classname($this->params);
		return $component->display();
	}	
}