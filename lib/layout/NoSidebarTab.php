<?php
/**
 * NoSidebarTab.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class is used to display a whole tab without sidebar
 * @package TabLayout
 */
class NoSidebarTab extends View{

    /**
     * Display the tab
     * @param array $data The data to inject in the view
     */
	public static function make($data){
		if(is_array($data['page']) && isset($data['page']['content']))
			$data['page'] = $data['page']['content'];
			
		return parent::make(Theme::getSelected()->getView('tabs-layout/tabs-no-sidebar.tpl'), $data);
	}
}