<?php

abstract class Widget extends Controller{    
	abstract public function display();
	
	public static function add($controllerAction, $order, $widgetAction){
		EventManager::on("$controllerAction.$order", $widgetAction);
	}
}