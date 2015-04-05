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
			$menus = Menu::getVisibleMenus($user);
			
			foreach($menus as $menu){
				// Get the menu items
				$menu->getVisibleItems();
			}
			
			// Trigger an event to add or remove menus from plugins 
			$event = new Event(self::EVENT_AFTER_GET_MENUS, array(
				'menus' => $menus
			));
			EventManager::trigger($event);
			$menus = $event->getData('menus');
		}
		
		// Get the user menu
		if(Session::logged()){
			$userMenu = array(
				'myProfile' => array(
					'url' => Router::getUri('UserProfileController.display', array('userId' => $user->id)),
					'label' => Lang::get('main.menu-my-profile'),
				),
				'editProfile' => array(
					'url' => Router::getUri('UserProfileController.edit', array('userId' => $user->id)),
					'label' => Lang::get('main.menu-edit-profile'),
				),	
				'change-password' => array(
					'url' => Router::getUri('UserPorfileController.changePassword'),
					'label' => Lang::get('main.menu-change-password'),
					'target' => 'dialog',
				),
				'logout' => array(
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
				$adminMenu = array();
				if($user->isAllowed('admin.all')){
					$adminMenu['settings'] = array(
						'url' => Router::getUri('AdminController.settings'),
						'label' => Lang::get('main.menu-admin-settings-title'),
					);
				}
				
				if($user->isAllowed('admin.users') || $user->isAllowed('admin.all')){
					$adminMenu['users'] = array(
						'url' => Router::getUri('UserController.index'),
						'label' => Lang::get('main.menu-admin-users-title'),
					);

					$adminMenu['permissions'] = array(
						'url' => Router::getUri('PermissionController.index'),
						'label' => Lang::get('main.menu-admin-roles-title'),
					);
				}
					
				if($user->isAllowed('admin.themes') || $user->isAllowed('admin.all')){
					$adminMenu['display'] = array(
						'url' => Router::getUri('DisplayController.index'),
						'label' => Lang::get('main.menu-admin-display-title'),
					);
				}
					
				if($user->isAllowed('admin.all')){
					$adminMenu['plugins'] = array(
						'url' => Router::getUri('PluginController.index'),
						'label' => Lang::get('main.menu-admin-plugins-title'),
					);
				}
				
				if($user->isAllowed('admin.languages') || $user->isAllowed('admin.all')){
					$adminMenu['language'] = array(
						'url' => Router::getUri('LanguageController.index'),
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