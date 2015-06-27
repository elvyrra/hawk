<?php

class MainMenuWidget extends Widget{
	const EVENT_AFTER_GET_MENUS = 'menu.after_get_items';
	const EVENT_AFTER_GET_USER_MENU = 'menu.after_get_user_items';
	
	/**
	 * Display the main menu
	 * */
	public function display(){
		$user = Session::getUser();
		if(!$user->canAccessApplication()){
			$menus = array();
		}
		else{
			// Get the menus 
			$menus = Menu::getAvailableMenus($user);

			
			// Trigger an event to add or remove menus from plugins 
			$event = new Event(self::EVENT_AFTER_GET_MENUS, array(
				'menus' => $menus
			));
			EventManager::trigger($event);
			$menus = $event->getData('menus');
		}
		
		$userMenu = $adminMenu = array();
		// Get the user menu
		if(Session::logged()){
			$userMenu = array(
				'myProfile' => array(
					'icon' => 'user',
					'url' => Router::getUri('UserProfileController.display', array('userId' => $user->id)),
					'label' => Lang::get('main.menu-my-profile'),
				),
				'editProfile' => array(
					'icon' => 'pencil',
					'url' => Router::getUri('UserProfileController.edit', array('userId' => $user->id)),
					'label' => Lang::get('main.menu-edit-profile'),
				),	
				'change-password' => array(
					'icon' => 'lock',
					'url' => Router::getUri('change-password'),
					'label' => Lang::get('main.menu-change-password'),
					'target' => 'dialog',
				),
				'logout' => array(
					'icon' => 'sign-out',
					'url' => Router::getUri('LoginController.logout'),
					'label' => Lang::get('main.menu-logout'),
					'class' => 'real-link'
				),			
			);
			$event = new Event(self::EVENT_AFTER_GET_USER_MENU, array(
				'menus' => $userMenu
			));
			$userMenu = $event->getData('menus');
			
			$user = Session::getUser();
			if($user->isAllowed('admin')){
				
				if($user->isAllowed('admin.all')){
					$adminMenu['settings'] = array(
						'icon' => 'cog',
						'url' => Router::getUri('AdminController.settings'),
						'label' => Lang::get('main.menu-admin-settings-title'),
					);
				}
				
				if($user->isAllowed('admin.users') || $user->isAllowed('admin.all')){
					$adminMenu['users'] = array(
						'icon' => 'users',
						'url' => Router::getUri('manage-users'),
						'label' => Lang::get('main.menu-admin-users-title'),
					);

					$adminMenu['permissions'] = array(
						'icon' => 'ban',
						'url' => Router::getUri('permissions'),
						'label' => Lang::get('main.menu-admin-roles-title'),
					);
				}
					
				if($user->isAllowed('admin.themes') || $user->isAllowed('admin.all')){
					$adminMenu['display'] = array(
						'icon' => 'paint-brush',
						'url' => Router::getUri('manage-themes'),
						'label' => Lang::get('main.menu-admin-display-title'),
					);
				}
					
				if($user->isAllowed('admin.all')){
					$adminMenu['plugins'] = array(
						'icon' => 'plug',
						'url' => Router::getUri('manage-plugins'),
						'label' => Lang::get('main.menu-admin-plugins-title'),
					);
				}
				
				if($user->isAllowed('admin.languages') || $user->isAllowed('admin.all')){
					$adminMenu['language'] = array(
						'icon' => 'flag',
						'url' => Router::getUri('manage-languages'),
						'label' => Lang::get('main.menu-admin-language-title')
					);
				}
			}
		}
		return View::make(ThemeManager::getSelected()->getView('main-menu.tpl'), array(
			'menus' => $menus,
			'user' => $user,
			'userMenu' => $userMenu,
			'adminMenu' => $adminMenu,
			'logo' => Option::get('main.logo') ? USERFILES_PLUGINS_URL . 'main/' . Option::get('main.logo') : ''
		));
	}
	
}