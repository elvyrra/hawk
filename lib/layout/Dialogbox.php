<?php
/**
 * Dialogbox.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to display a dialogbox
 *
 * @package Layout
 */
class Dialogbox {

    /**
     * Display the dialogbox
     *
     * @param array $data The data to inject in the view
     *
     * @return string The generated HTML
     */
    public static function make($data){
        return View::make(Theme::getSelected()->getView('dialogbox.tpl'), $data);
    }
}