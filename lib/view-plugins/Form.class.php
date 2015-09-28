<?php
/**
 * Form.class.php
 * @author Elvyrra SAS
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display a form in a view
 * @package View\Plugins
 */
class Form extends \Hawk\ViewPlugin{
	/**
	 * The id of the form to display
	 */
	public $id,

	/**
	 * The content of the form to display
	 */
	$content;

	/**
	 * Display the form
	 */
	public function display(){
		$form = \Hawk\Form::getInstance($this->id);
		if($form){
			if(isset($this->content)){
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