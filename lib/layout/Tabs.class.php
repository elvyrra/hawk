<?php
/**
 * LeftSidebarTab.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class is used to display a whole tab with a sidebar on left
 * @package TabLayout
 */
class Tabs extends View{
	
	/**
	 * Display the tab
	 * @param array $data The data to inject in the view
	 */
	public static function make($data){
		if(empty($data['id'])){
			$data['id'] = uniqid();
		}
		foreach($data['tabs'] as $i => &$tab){			
			if(empty($tab['id'])){
				$tab['id'] = uniqid();
			}
			if(empty($data['selected'])){
				$data['selected'] = $i;
			}
		}		
		return parent::make(Theme::getSelected()->getView('tabs.tpl'), $data);
	}
}