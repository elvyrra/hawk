<?php
/**
 * Form.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display a form in a view
 *
 * @package View\Plugins
 */
class Form extends \Hawk\ViewPlugin{
    /**
     * The id of the form to display
     *
     * @var string
     */
    public $id,

    /**
     * The content of the form to display
     *
     * @var string
     */
    $content;

    /**
     * Display the form
     */
    public function display(){
        $form = \Hawk\Form::getInstance($this->id);
        if($form) {
            if(isset($this->content)) {
                return $form->wrap($this->content);
            }
            else{
                return $form->display();
            }
        }
        else{
            return '';
        }
    }
}
