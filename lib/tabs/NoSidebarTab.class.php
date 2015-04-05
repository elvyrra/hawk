<?php

class NoSidebarTab extends View{
	public static function make($data){
		if(is_array($data['page']) && isset($data['page']['content']))
			$data['page'] = $data['page']['content'];
			
		return parent::make(ThemeManager::getSelected()->getView('tabs-layout/tabs-no-sidebar.tpl'), $data);
	}
}