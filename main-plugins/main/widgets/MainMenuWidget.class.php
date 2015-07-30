<?php

class MainMenuWidget extends Widget{
	const EVENT_AFTER_GET_MENUS = 'menu.after_get_items';
	const EVENT_AFTER_GET_USER_MENU = 'menu.after_get_user_items';

	const USER_MENU_NAME = 'user';
	const ADMIN_MENU_NAME = 'admin';
	
	/**
	 * Display the main menu
	 * */
	public function display(){
		$user = Session::getUser();
		$menus = $userMenus = array();

		if(Session::isConnected()) {
			unset($userMenus['user']);
		}
	
		$event = new Event(self::EVENT_AFTER_GET_USER_MENU, array(
			'menus' => $userMenus
		));
		$userMenus = $event->getData('menus');

		if($user->canAccessApplication()){			
			// Get the menus 
			$menus = Menu::getAvailableMenus($user, 'name');

			// Get the user menu
			if(Session::isConnected()){
				$menus[self::USER_MENU_NAME]->label = $user->getUsername();
				$userMenus[self::USER_MENU_NAME] = $menus[self::USER_MENU_NAME];				
			}
			// remove the user menu from applications menus
			unset($menus[self::USER_MENU_NAME]);

			// put the admin menu in user menu
			if(!empty($menus[self::ADMIN_MENU_NAME])){
				$userMenus[self::ADMIN_MENU_NAME] = $menus[self::ADMIN_MENU_NAME];
				unset($menus[self::ADMIN_MENU_NAME]);
			}

			// Trigger an event to add or remove menus from plugins 
			$event = new Event(self::EVENT_AFTER_GET_MENUS, array(
				'menus' => $menus,
				'userMenus' => $userMenus
			));
			EventManager::trigger($event);
			$menus = $event->getData('menus');
			$userMenus = $event->getData('userMenus');
		}


		return View::make(ThemeManager::getSelected()->getView('main-menu.tpl'), array(
			'menus' => $menus,
			'user' => $user,
			'userMenus' => $userMenus,
			'logo' => Option::get('main.logo') ? USERFILES_PLUGINS_URL . 'main/' . Option::get('main.logo') : ''
		));
	}
	
}