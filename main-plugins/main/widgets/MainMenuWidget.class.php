<?php

class MainMenuWidget extends Widget{
	const EVENT_AFTER_GET_MENUS = 'menu.after_get_items';
	const EVENT_AFTER_GET_USER_MENU = 'menu.after_get_user_items';
	
	/**
	 * Display the main menu
	 * */
	public function display(){
		$user = Session::getUser();
		$menus = $userMenus = array();


		if(Session::isConnected()){
			// Get the user menu items
			$userMenus[] = new Menu(array(
				'name' => 'user',				
				'label' => $user->getUsername(),
				'visibleItems' => array(
					new MenuItem(array(
						'name' => 'profile',
						'icon' => 'user',
						'url' => Router::getUri('edit-profile', array('userId' => $user->id)),
						'labelKey' => 'main.menu-my-profile',
						'target' => 'dialog'
					)),				
					new MenuItem(array(
						'name' => 'change-password',
						'icon' => 'lock',
						'url' => Router::getUri('change-password'),
						'labelKey' => 'main.menu-change-password',
						'target' => 'dialog',
					)),

					new MenuItem(array(
						'name' => 'logout',
						'icon' => 'sign-out',
						'url' => 'javascript: location = app.getUri(\'logout\');',
						'labelKey' => 'main.menu-logout',
					))
				)		
			));
			$event = new Event(self::EVENT_AFTER_GET_USER_MENU, array(
				'menus' => $userMenus
			));
			$userMenus = $event->getData('menus');
		}


		if($user->canAccessApplication()){			
			// Get the menus 
			$menus = Menu::getAvailableMenus($user);

			$adminMenuId = Menu::getByName('admin')->id;
			if(!empty($menus[$adminMenuId])){
				$userMenus[] = $menus[$adminMenuId];
				unset($menus[$adminMenuId]);
			}

			// Trigger an event to add or remove menus from plugins 
			$event = new Event(self::EVENT_AFTER_GET_MENUS, array(
				'menus' => $menus
			));
			EventManager::trigger($event);
			$menus = $event->getData('menus');
		}

		

		return View::make(ThemeManager::getSelected()->getView('main-menu.tpl'), array(
			'menus' => $menus,
			'user' => $user,
			'userMenus' => $userMenus,
			'logo' => Option::get('main.logo') ? USERFILES_PLUGINS_URL . 'main/' . Option::get('main.logo') : ''
		));
	}
	
}