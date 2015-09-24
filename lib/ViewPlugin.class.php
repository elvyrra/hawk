<?php
/**
 * ViewPlugin.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This abstract class defines the view plugins. It must be inherited by any class that defines any view plugin. 
 * This class is called when, in a view, you write something like that :
 * {pluginName param="value1" param2="{$variable}" }
 * @package View\Plugins
 */
abstract class ViewPlugin{
    use Utils;

	/**
	 * The plugin instance parameters
	 */
	protected $params;
	
	/**
	 * Contructor
	 * @param array $params The instance parameters
	 */
    public function __construct($params = array()){
        $this->params = $params;
        $this->map($params);
    }

	/**
	 * Display the plugin. This abstract method must be overriden in each inherited class and return the HTML content corresponding to the instance
	 * @return string The HTML result to display
	 */
    abstract public function display();
}