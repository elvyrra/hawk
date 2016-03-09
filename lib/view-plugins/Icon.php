<?php
/**
 * Icon.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\View\Plugins;

/**
 * This class is used in views to display an icon
 *
 * @package View\Plugins
 */
class Icon extends \Hawk\ViewPlugin{
    /**
     * Display the button
     *
     * @return string The html result describing the button
     */
    public function display(){
        return \Hawk\Icon::make($this->params);
    }
}
