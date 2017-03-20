<?php
/**
 * Tabs.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to display a tabset
 *
 * @package Layout
 */
class Tabs {

    /**
     * Display the tabset
     *
     * @param array $data The data to inject in the view
     *
     * @return string The generated HTML
     */
    public static function make($data){
        if(empty($data['id'])) {
            $data['id'] = uniqid();
        }
        foreach($data['tabs'] as $i => &$tab){
            if(empty($tab['id'])) {
                $tab['id'] = uniqid();
            }
            if(empty($data['selected'])) {
                $data['selected'] = $i;
            }
        }
        return View::make(Theme::getSelected()->getView('tabs.tpl'), $data);
    }
}