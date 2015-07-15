<?php

class LeftSidebarTab extends View{
	
	public static function make($data){
		if(!isset($data['sidebar']['class'])){
			$data['sidebar']['class'] = 'col-md-3 col-lg-2';
		}

		if(!isset($data['page']['class'])){
			$data['page']['class'] = 'col-md-9 col-lg-10';
		}
		
		return parent::make(ThemeManager::getSelected()->getView('tabs-layout/tabs-sidebar-left.tpl'), $data);
	}
}