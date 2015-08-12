<?php
/**
 * ViewPluginWidget.class.php
 * @author Elvyrra SAS
 */


/**
 * This class is used to display a widget in a view
 * @package View\Plugins
 */
class ViewPluginWidget extends ViewPlugin{
	
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