<?php
/**
 * MainMenuWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Main;

/**
 * Main menu widget
 *
 * @package Plguins\Main
 */
class MainMenuWidget extends Widget{
    const EVENT_AFTER_GET_MENUS = 'menu.after-get-items';

    /**
     * Display the main menu. The menu is separated in two :
     * <ul>
     *     <li> The applications (plugins) </li>
     *     <li>The user menu (containing the access to user data, and admin data if the user is administrator)</li>
     * </ul>
     */
    public function display(){
        $user = App::session()->getUser();

        $menus= array(
            'applications' => array(),
            'user' => array()
        );

        if($user->canAccessApplication()) {
            // Get the menus
            $items = MenuItem::getAvailableItems($user);

            // Filter the menus that have no action and no item
            $items = array_filter($items, function ($item) {
                return $item->action || count($item->visibleItems) > 0;
            });

            foreach($items as $id => $item){
                if($id == MenuItem::USER_ITEM_ID) {
                    $item->label = $user->getUsername();
                }

                if(in_array($id, array(MenuItem::USER_ITEM_ID, MenuItem::ADMIN_ITEM_ID))) {
                    $menus['user'][$item->order] = $item;
                }
                else{
                    $menus['applications'][$item->order] = $item;
                }
            }
        }

        if(!App::session()->isLogged()) {
            $menus['user'][] = new MenuItem(array(
                'plugin' => 'main',
                'name' => 'login',
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