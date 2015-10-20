<?php

namespace Hawk\Plugins\Main;

class MainMenuWidget extends Widget{
	const EVENT_AFTER_GET_MENUS = 'menu.after_get_items';
	const EVENT_AFTER_GET_USER_MENU = 'menu.after_get_user_items';

	/**
	 * Display the main menu. The menu is separated in two : The applications (plugins), and the user menu (containing the access to user data, and admin data if the user is administrator)
	 * */
	public function display(){
		$user = Session::getUser();
		
		$menus= array(
			'applications' => array(),
			'user' => array()
		);

		if($user->canAccessApplication()){			
			// Get the menus 
			$items = MenuItem::getAvailableItems($user);

			// Filter the menus that have no action and no item
			$items = array_filter($items, function($item){
				return $item->action || count($item->visibleItems) > 0;
			});

			foreach($items as $id => $item){
				if($id == MenuItem::USER_ITEM_ID){
					$item->label = $user->getUsername();
				}

				if(in_array($id, array(MenuItem::USER_ITEM_ID, MenuItem::ADMIN_ITEM_ID))){
					$menus['user'][$item->order] = $item;
				}
				else{
					$menus['applications'][$item->order] = $item;
				}
			}			
		}
		
		if(!Session::isConnected()){
			$menus['user'][] = new MenuItem(array(
				'id' => uniqid(),
				'labelKey' => 'main.login-menu-title',
				'action' => 'login',
				'target' => 'dialog',
			));
		}

		// Trigger an event to add or remove menus from plugins 
		$event = new Event(self::EVENT_AFTER_GET_MENUS, array(
			'menus' => $menus
		));

		$event->trigger();
		$menus = $event->getData('menus');

		return View::make(Theme::getSelected()->getView('main-menu.tpl'), array(
			'menus' => $menus,
			'logo' => Option::get('main.logo') ? Plugin::current()->getUserfilesUrl(Option::get('main.logo')) : ''
		));
	}
	
}