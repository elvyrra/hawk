<?php
/**
 * Widget.class.php
 */



/**
 * This abstract class describes the bahavior of widgets. 
 * Widgets can be a little part of your page you want to use several times.
 * It can be another thing : a further feature that you call on a controller action.
 * @package Core
 */
abstract class Widget extends Controller{    
	/**
	 * Display the widget
	 * @return string The HTML result of the widget displaying
	 */
	abstract public function display();
	
	/**
	 * Add the widget on controller action
	 * @param string $controllerAction The controller that calls the widget
	 * @param string $order (before | after) to define if the widget will be called before or after controller action execution
	 * @param string $widgetAction The action to perform to display the widget
	 */
	public static function add($controllerAction, $order, $widgetAction){
		EventManager::on("$controllerAction.$order", $widgetAction);
	}
}