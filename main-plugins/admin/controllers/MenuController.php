<?php
/**
 * MenuController.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk\Plugins\Admin;

/**
 * This controller is used to manage the main application menu
 */
class MenuController extends Controller{
	/**
	 * Customize the menu
	 */
	public function index(){
		$items = MenuItem::getAll();

		$form = new Form(array(
			'id' => 'set-menus-form',
			'action' => Router::getUri('set-menu'),
			'inputs' => array(
				new HiddenInput(array(
					'name' => 'data',
					'default' => json_encode($items, JSON_NUMERIC_CHECK),
					'attributes' => array(
						'ko-value' => 'ko.toJSON(items)'
					),
				)),				

				new SubmitInput(array(
					'name' => 'valid',
					'value' => Lang::get('main.valid-button'),
				)),
			),

			'onsuccess' => 'app.refreshMenu()'
		));

		if(!$form->submitted()){
			Lang::addKeysToJavaScript('admin.plugins-advert-menu-changed');
			return View::make(Plugin::current()->getView('sort-main-menu.tpl'), array(
				'form' => $form,				
			));
		}
		else{
			try {
				$items = MenuItem::getAll('id');

				$data = json_decode($form->getData('data'), true);

				foreach($data as $line){
					$item = $items[$line['id']];
					$item->set(array(
						'active' => $line['active'],
						'parentId' => $line['parentId'],
						'order' => $line['order']
					));
					$item->save();
				}

				return $form->response(Form::STATUS_SUCCESS, Lang::get('admin.sort-menu-success'));
			} 
			catch (Exception $e) {
				return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.sort-menu-error'));
			}
			
		}
	}

	/**
	 * Remove a custom menu item
	 */
	public function removeCustomMenuItem(){
		$item = MenuItem::getById($this->itemId);
		
		if($item && $item->plugin === 'custom'){
			$item->delete();

			foreach(Language::getAll() as $language){
				$language->removeTranslations(array(
					'custom' => array('menu-item-' . $item->name . '-title')
				));
			}
		}
		else{
			Response::setHttpCode(412);
		}
	}

	/**
	 * Generate the form to create / edit a custom menu item
	 */
	public function customMenuItemForm($itemId){
		$item = MenuItem::getById($itemId);

		$name = $item ? $item->name : uniqid();

		$param = array(
			'id' => 'menu-item-form-' . $itemId,
			'class' => 'menu-item-form',
			'object' => $item,
			'model' => 'MenuItem',
			'reference' => array('id' => $itemId),
			'action' => Router::getUri('edit-menu', array('itemId' => $itemId)),
			'fieldsets' => array(
				'parameters' => array(
					new HiddenInput(array(
						'name' => 'plugin',
						'value' => 'custom'
					)),

					new HiddenInput(array(
						'name' => 'parentId',
						'default' => '0'
					)),

					new HiddenInput(array(
						'name' => 'active',
						'default' => '0'
					)),

					new HiddenInput(array(
						'name' => 'name',
						'default' => $name,
					)),

					new HiddenInput(array(
						'name' => 'labelKey',
						'default' => 'custom.menu-item-' . $name . '-title'
					))
				),

				'submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button')
					)),

					new ButtonInput(array(
						'name' => 'cancel',
						'onclick' => 'app.dialog("close")',
						'value' => Lang::get('main.cancel-button'),
						'notDisplayed' => ! $itemId
					))
				),
			),

			'onsuccess' => 'app.forms["set-menus-form"].node.trigger("register-custom-item", data);'
		);

		foreach(Language::getAllActive() as $language){
			$param['fieldsets']['parameters'][] = new TextInput(array(
				'name' => 'label[' . $language->tag . ']',
				'independant' => true,
				'label' => Lang::get('admin.menu-item-form-label', array('language' => $language->tag)),
				'default' => $itemId ? Lang::get('custom.menu-item-' . $name . '-title', null, null, $language->tag) : ''
			));
		}

		return new Form($param);
	}


	/**
	 * Edit a custom menu item
	 */
	public function editCustomMenuItem(){
		$form = $this->customMenuItemForm($this->itemId);

		if(!$form->submitted()){
			return View::make(Theme::getSelected()->getView('dialogbox.tpl'), array(
				'page' => $form->display(),
				'title' => Lang::get('admin.menu-item-form-edit-title'),
				'icon' => 'pencil'
			));
		}
		else{
			if($form->check()){
				try{
					$form->register(Form::NO_EXIT);

					// Register the translations of the menu
					foreach(Request::getBody('label') as $tag => $translation){
						Language::getByTag($tag)->saveTranslations(array(
							$form->getData('plugin') => array(
								'menu-item-' . $form->getData('name') . '-title' => $translation
							)
						));
					}

					$form->addReturn(get_object_vars($form->object));
					$form->addReturn('label', Request::getBody('label')[LANGUAGE]);
					return $form->response(Form::STATUS_SUCCESS, Lang::get('admin.menu-item-form-success'));
				}
				catch(\Exception $e){
					return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.menu-item-form-error'));
				}
			}		
		}
	}
}