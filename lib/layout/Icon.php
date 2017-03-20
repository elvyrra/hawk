<?php
/**
 * Icon.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to display an icon
 *
 * @package Layout
 */
class Icon {

    /**
     * Display the icon
     *
     * @param array $data The data to inject in the view
     *
     * @return string The generated HTML
     */
    public static function make($data){
        if(!isset($data['size'])) {
            $data['size'] = '';
        }
        if(!isset($data['class'])) {
            $data['class'] = '';
        }
        $data['param'] = $data;

        unset($data['param']['size'], $data['param']['class'], $data['param']['icon']);

        return View::make(Theme::getSelected()->getView('icon.tpl'), $data);
    }
}