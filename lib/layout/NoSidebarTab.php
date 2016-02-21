<?php
/**
 * NoSidebarTab.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to display a whole tab without sidebar
 *
 * @package Layout
 */
class NoSidebarTab extends View{

    /**
     * Display the tab
     *
     * @param array $data The data to inject in the view
     *
     * @return string The generated HTML
     */
    public static function make($data){
        if(is_array($data['page']) && isset($data['page']['content']))
        $data['page'] = $data['page']['content'];

        return parent::make(Theme::getSelected()->getView('tabs-layout/tabs-no-sidebar.tpl'), $data);
    }
}
