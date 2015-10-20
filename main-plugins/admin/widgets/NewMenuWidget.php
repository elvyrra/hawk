<?php
/**
 * NewMenu.php
 */

namespace Hawk\Plugins\Admin;

class NewMenuWidget extends Widget{
	public function form($itemId){
		$name = uniqid();

		return new Form(array(
			'id' => 'new-menu-form',
			'model' => 'MenuItem',
			'action' => Router::getUri('edit-item', array('itemId' => $itemId)),
			'reference' => array('id' => $itemId),
			'fieldsets' => array(
				'parameters' => array(
					new HiddenInput(array(
						'name' => 'plugin',
						'value' => 'custom'
					)),

					new HiddenInput(array(
						'name' => 'parentId',
						'value' => 0
					)),

					new HiddenInput(array(
						'name' => 'active',
						'value' => 0
					)),

					new HiddenInput(array(
						'name' => 'name',
						'value' => $name
					)),

					new HiddenInput(array(
						'name' => 'labelKey',
						'value' => 'custom.menu-item-' . $name . '-title'
					))
				),

				'labels' => array_map(function($language){
					return new TextInput(array(
						'name' => 'label[' . $language->tag . ']',
						'independant' => true,
						'label' => Lang::get('admin.new-menu-form-label', array('language' => $language->tag))
					));
				}, Language::getAllActive()),

				'submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button')
					))
				)
			),
		));
	}

	public function display(){
		$form = ThemeController::getInstance()->customMenuItemForm(0);
		return $form->display();
	}
}