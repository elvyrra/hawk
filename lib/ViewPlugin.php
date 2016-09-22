<?php
/**
 * ViewPlugin.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This abstract class defines the view plugins. It must be inherited by any class that defines any view plugin.
 * This class is called when, in a view, you write something like that :
 * {pluginName param="value1" param2="{$variable}" }
 *
 * @package View\Plugins
 */
abstract class ViewPlugin{
    use Utils;

    /**
     * The filename of the view that calls the plugin
     *
     * @var string
     */
    protected $viewFile,

    /**
     * The data in the parent view that calls the plugin
     *
     * @var array
     */
    $viewData,

    /**
     * The plugin instance parameters
     *
     * @var array
     */
    $params;

    /**
     * Contructor
     *
     * @param string $viewFile The view file that instances this plugin
     * @param array  $viewData The data injected in the view
     * @param array  $params   The instance parameters
     */
    public function __construct($viewFile, $viewData = array(), $params = array()){
        $this->viewFile = $viewFile;
        $this->viewData = $viewData;

        if(isset($params['_attrs'])) {
            $params = $params['_attrs'];
        }

        $this->params = $params;
        $this->map($params);
    }

    /**
     * Display the plugin. This abstract method must be overriden in each inherited class and return the HTML content corresponding to the instance
     *
     * @return string The HTML result to display
     */
    abstract public function display();

    /**
     * Display the plugin. This abstract method must be overriden in each inherited class and return the HTML content corresponding to the instance
     *
     * @return string The HTML result to display
     */
    public function __toString(){
        try {
            return $this->display();
        }
        catch(\Exception $e) {
            return nl2br($e->getTraceAsString());
        }
    }
}
