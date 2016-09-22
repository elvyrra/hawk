<?php
/**
 * Input.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\View\Plugins;

use \Hawk\CheckboxInput as CheckboxInput;

/**
 * This class is used in views to display a button
 *
 * @package View\Plugins
 */
class Input extends \Hawk\ViewPlugin{
    /**
     * The other parameters to apply
     *
     * @var array
     */
    public $params = array();

    /**
     * Display the button
     *
     * @return string The html result describing the button
     */
    public function display() {

        if(empty($this->params['type'])) {
            $this->params['type'] = 'text';
        }

        $inputClass = '\\Hawk\\' . ucfirst($this->params['type']) . 'Input';

        unset($this->params['type']);

        $classVars = get_class_vars($inputClass);
        $param = array();

        foreach($this->params as $key => $value) {
            if(isset($classVars[$key])) {
                $param[$key] = $value;
            }
            else {
                if(empty($param['attributes'])) {
                    $param['attributes'] = array();
                }

                $param['attributes'][$key] = $value;
            }
        }

        if(!isset($param['id'])) {
            $param['id'] = uniqid();
        }


        $input = new $inputClass($param);

        return $input->display();
    }
}
