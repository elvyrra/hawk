<?php
/**
 * Button.class.php
 * @author Elvyrra SAS
 */

namespace Hawk\View\Plugins;

/**
 * This class is used in views to display a button
 * @package View\Plugins
 */
class Button extends \Hawk\ViewPlugin{
	/**
	 * The class attribute to apply to the button
	 */
	public $class = '',

	/**
	 * The icon to display in the button
	 */
	$icon = '',

	/**
	 * The text to display in the button
	 */
	$label = '',

	/**
	 * The other parameters to apply
	 */
	$param = array();

	/**
	 * Constructor
	 * @param array $params The button parameters
	 */
	public function __construct($params = array()){
		if(isset($params['data'])){
			$params = $params['data'];
		}
		parent::__construct($params);
	}
	
	/**
	 * display the button
	 * @return string The html result describing the button
	 */
	public function display(){
		return \Hawk\View::make(\Hawk\ThemeManager::getSelected()->getView('button.tpl'), array(
			'class' => $this->class,
			'icon' => $this->icon,
			'label' => $this->label,
			'param' => $this->params
		));
	}	
}
