<?php

class RightSidebarTab extends View{
	const DEFAULT_CONTENT_SIZE = 10;
	const DEFAULT_SIDEBAR_SIZE = 2;
	const MAX_SIZE = 12;
	
	public static function make($data){
		if(isset($data['sidebar']['size'])){
			if($data['sidebar']['size'] > self::MAX_SIZE){
				$data['sidebar']['size'] = self::DEFAULT_SIDEBAR_SIZE;
			}
			$data['pageSize'] = self::MAX_SIZE - $data['sidebar']['size'];
		}
		else{
			$data['pageSize'] = self::DEFAULT_CONTENT_SIZE;
			$data['sidebar']['size'] = self::DEFAULT_SIDEBAR_SIZE;
		}
		
		return parent::make(ThemeManager::getSelected()->getView('tabs-layout/tabs-sidebar-right.tpl'), $data);
	}
}