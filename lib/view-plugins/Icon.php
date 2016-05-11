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
    public function display() {
        if(!empty($this->params['href'])) {
            $this->params['data-href'] = $this->params['href'];
            unset($this->params['href']);
        }
        if(!empty($this->params['target'])) {
            $this->params['data-target'] = $this->params['target'];
            unset($this->params['target']);
        }

        return \Hawk\Icon::make($this->params);
    }
}
