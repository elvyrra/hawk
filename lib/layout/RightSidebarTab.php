<?php
/**
 * RightSidebarTab.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to display a whole tab with a sidebar on right
 *
 * @package Layout
 */
class RightSidebarTab {
    /**
     * Display the tab
     *
     * @param array $data The data to inject in the view
     *
     * @return string The generated HTML
     */
    public static function make($data){
        if(!isset($data['sidebar']['class'])) {
            $data['sidebar']['class'] = 'col-md-3 col-lg-2';
        }

        if(!isset($data['page']['class'])) {
            $data['page']['class'] = 'col-md-9 col-lg-10';
        }

        return View::make(Theme::getSelected()->getView('tabs-layout/tabs-sidebar-right.tpl'), $data);
    }
}
