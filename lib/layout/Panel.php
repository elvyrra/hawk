<?php
/**
 * LeftSidebarTab.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class is used to display a whole tab with a sidebar on left
 * @package TabLayout
 */
class Panel extends View{
	
	/**
	 * Display the tab
	 * @param array $data The data to inject in the view
	 */
	public static function make($data){
		if(empty($data['id'])){
			$data['id'] = uniqid();
		}
		if(empty($data['type'])){
			$data['type'] = 'info';
		}
		return parent::make(Theme::getSelected()->getView('panel.tpl'), $data);
	}
}