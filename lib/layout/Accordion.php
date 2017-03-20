<?php
/**
 * Accordion.php
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
class Accordion {

    /**
     * Display the accordion
     *
     * @param array $data The data to inject in the view
     *
     * @return string The generated HTML
     */
    public static function make($data){
        if(empty($data['id'])) {
            $data['id'] = uniqid();
        }
        foreach($data['panels'] as $i => &$panel){
            if(empty($panel['id'])) {
                $panel['id'] = uniqid();
            }
            if(empty($data['selected'])) {
                $data['selected'] = $i;
            }
        }

        return View::make(Theme::getSelected()->getView('accordion.tpl'), $data);
    }
}