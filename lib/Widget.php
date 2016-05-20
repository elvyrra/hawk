<?php
/**
 * Widget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This abstract class describes the bahavior of widgets.
 * Widgets can be a little part of your page you want to use several times.
 * It can be another thing : a further feature that you call on a controller action.
 *
 * @package Core
 */
abstract class Widget extends Controller{

    /**
     * Constructor
     *
     * @param array $param The widget parameters
     */
    protected function __construct($param = array()) {
        parent::__construct($param);
    }

    /**
     * Display the widget
     *
     * @return string The HTML result of the widget displaying
     */
    abstract public function display();

    /**
     * Display the widget
     *
     * @return string The HTML result of the widget displaying
     */
    public function __toString(){
        return $this->display();
    }
}
