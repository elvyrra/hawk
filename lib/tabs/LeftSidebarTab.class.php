<?php
/**
 * LeftSidebarTab.class.php
 * @author Elvyrra SAS
 */

/**
 * This class is used to display a whole tab with a sidebar on left
 * @package TabLayout
 */
class LeftSidebarTab extends View{
	
	/**
	 * Display the tab
	 * @param array $data The data to inject in the view
	 */
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