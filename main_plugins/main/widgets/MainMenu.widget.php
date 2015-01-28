<?php

class MainMenuWidget extends Widget{
	const EVENT_AFTER_GET_MENUS = 'menu.after_get_items';
	const EVENT_AFTER_GET_USER_MENU = 'menu.after_get_user_items';
	
	/**
	 * Display the main menu
	 * */
	public function display(){
		$user = Session::getUser();

		// Get the menus 
		$menus = MenuModel::getVisibleMenus($user);
		
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
		
		// Get the user menu
		if(Session::logged()){
			$userMenu = array(
				'myProfile' => array(
					'url' => Router::getUri('UserProfileController.display', array('userId' => $user->id)),
					'label' => Lang::get('main.menu-my-profile'),
					'target' => 'newtab'
				),
				'editProfile' => array(
					'url' => Router::getUri('UserProfileController.edit', array('userId' => $user->id)),
					'label' => Lang::get('main.menu-edit-profile'),
					'target' => 'newtab'
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
			
			if(Session::getUser()->canDo('admin.manage')){
				$adminMenu = array(
					'settings' => array(
						'url' => Router::getUri('AdminController.settings'),
						'label' => Lang::get('main.menu-admin-settings-title'),
						'target' => 'newtab',
					),
					'users' => array(
						'url' => Router::getUri('UserController.index'),
						'label' => Lang::get('main.menu-admin-users-title'),
						'target' => 'newtab',
					),
					'permissions' => array(
						'url' => Router::getUri('UserController.permissions'),
						'label' => Lang::get('main.menu-admin-roles-title'),
						'target' => 'newtab',
					),
					'display' => array(
						'url' => Router::getUri('DisplayController.index'),
						'label' => Lang::get('main.menu-admin-display-title'),
						'target' => 'newtab',
					),
					'plugins' => array(
						'url' => Router::getUri('PluginController.index'),
						'label' => Lang::get('main.menu-admin-plugins-title'),
						'target' => 'newtab',
					),
				);
			}
		}
		return View::make(ThemeManager::getSelected()->getView('main-menu.tpl'), array(
			'menus' => $menus,
			'user' => $user,
			'userMenu' => $userMenu,
			'adminMenu' => $adminMenu
		));
	}
	
}