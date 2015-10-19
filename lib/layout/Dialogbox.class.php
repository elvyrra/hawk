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
class Dialogbox extends View{
	
	/**
	 * Display the tab
	 * @param array $data The data to inject in the view
	 */
	public static function make($data){
		return parent::make(Theme::getSelected()->getView('dialogbox.tpl'), $data);
	}
}