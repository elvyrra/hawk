<?php
/**
 * Uri.php
 * @author Elvyrra SAS
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display an URI in the view
 * @package View\Plugins
 */
class Uri extends \Hawk\ViewPlugin{
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
        return \Hawk\App::router()->getUri($this->action, $this->params);
    }
}