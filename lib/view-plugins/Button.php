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
        if(!empty($this->params['href'])) {
            $this->params['data-href'] = $this->params['href'];
            unset($this->params['href']);
        }
        if(!empty($this->params['target'])) {
            $this->params['data-target'] = $this->params['target'];
            unset($this->params['target']);
        }
        if(empty($this->params['type'])) {
            $this->params['type'] = 'button';
        }

        return \Hawk\View::make(\Hawk\Theme::getSelected()->getView('button.tpl'), array(
            'class' => $this->class,
            'icon' => $this->icon,
            'label' => $this->label,
            'param' => $this->params
        ));
    }
}
