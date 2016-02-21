<?php
/**
 * Button.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\View\Plugins;

/**
 * This class is used in views to display a button
 *
 * @package View\Plugins
 */
class Button extends \Hawk\ViewPlugin{
    /**
     * The class attribute to apply to the button
     *
     * @var string
     */
    public $class = '',

    /**
     * The icon to display in the button
     *
     * @var string
     */
    $icon = '',

    /**
     * The text to display in the button
     *
     * @var string
     */
    $label = '',

    /**
     * The other parameters to apply
     *
     * @var array
     */
    $param = array();

    /**
     * Display the button
     *
     * @return string The html result describing the button
     */
    public function display(){
        return \Hawk\View::make(
            \Hawk\Theme::getSelected()->getView('button.tpl'), array(
            'class' => $this->class,
            'icon' => $this->icon,
            'label' => $this->label,
            'param' => $this->params
            )
        );
    }
}
