<?php
/**
 * Panem.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to display a panel
 *
 * @package Layout
 */
class Panel extends View{

    /**
     * Display the panem
     *
     * @param array $data The data to inject in the view
     *
     * @return string The generated HTML
     */
    public static function make($data){
        if(empty($data['id'])) {
            $data['id'] = uniqid();
        }
        if(empty($data['type'])) {
            $data['type'] = 'info';
        }
        return parent::make(Theme::getSelected()->getView('panel.tpl'), $data);
    }
}