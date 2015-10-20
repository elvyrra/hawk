<?php
/**
 * Widget.php
 * @author Elvyrra SAS
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display a widget in a view
 * @package View\Plugins
 */
class Widget extends \Hawk\ViewPlugin{
	
	/**
	 * The classname of the widget
	 */
	public $class;

	/**
	 * Display the widget
	 * @return string The displayed HTML
	 */
	public function display(){
		$classname = $this->class;
		$component = new $classname($this->params);
		return $component->display();
	}	
}