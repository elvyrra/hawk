<?php
/**
 * ViewPluginUri.class.php
 * @author Elvyrra SAS
 */

/**
 * This class is used to display an URI in the view
 * @package View\Plugins
 */
class ViewPluginUri extends ViewPlugin{
	/**
	 * The URI action or route name
	 */
	public $action;

	/**
	 * Display the URI
	 * @return string The found URI
	 */
    public function display(){
        unset($this->params['action']);
        return Router::getUri($this->action, $this->params);
    }
}