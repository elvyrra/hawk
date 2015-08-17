<?php
/**
 * ViewPluginForm.class.php
 * @author Elvyrra SAS
 */

/**
 * This class is used to display a form in a view
 * @package View\Plugins
 */
class ViewPluginForm extends ViewPlugin{
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
		$form = Form::getInstance($this->id);
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