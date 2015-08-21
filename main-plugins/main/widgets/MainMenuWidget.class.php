<?php


class MainMenuWidget extends Widget{
	const EVENT_AFTER_GET_MENUS = 'menu.after_get_items';
	const EVENT_AFTER_GET_USER_MENU = 'menu.after_get_user_items';

	const USER_MENU_ID = 1;
	const ADMIN_MENU_ID = 2;
	

	/**
	 * Display the main menu
	 * */
	public function display(){
		$user = Session::getUser();
		$menus = $userMenus = array();

		if($user->canAccessApplication()){			
			// Get the menus 
			$menus = Menu::getAvailableMenus($user);

			// Filter the menus that have no action and no item
			$menus = array_filter($menus, function($menu){
				return $menu->action || count($menu->visibleItems) > 0;
			});


			// Get the user menu
			if(Session::isConnected()){
				$menus[self::USER_MENU_ID]->label = $user->getUsername();
				$userMenus[self::USER_MENU_ID] = $menus[self::USER_MENU_ID];				
			}
			// remove the user menu from applications menus
			unset($menus[self::USER_MENU_ID]);

			// put the admin menu in user menu
			if(!empty($menus[self::ADMIN_MENU_ID])){
				$userMenus[self::ADMIN_MENU_ID] = $menus[self::ADMIN_MENU_ID];
				unset($menus[self::ADMIN_MENU_ID]);
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
		else{
			$userMenus = array(
				new Menu(array(
					'id' => uniqid(),
					'labelKey' => 'main.login',
					'action' => 'login',
					'target' => 'dialog',
				))
			);
		}


		return View::make(ThemeManager::getSelected()->getView('main-menu.tpl'), array(
			'groups' => array(
				'left' => $menus,
				'right' => $userMenus
			),
			'logo' => Option::get('main.logo') ? USERFILES_PLUGINS_URL . 'main/' . Option::get('main.logo') : ''
		));
	}
	
}