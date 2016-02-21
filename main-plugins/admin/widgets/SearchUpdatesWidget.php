<?php
/**
 * SearchUpdateWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\Plugins\Admin;

/**
 * This widget displays in the main menu a little div with the number of available updates
 *
 * @package Plugins\Admin
 */
class SearchUpdatesWidget extends Widget{
    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){

        // The number of updates
        $updates = array();
        $titles = array();

        $api = new HawkApi;

        $plugins = array_map(function ($plugin) {
            return $plugin->getDefinition('version');
        }, Plugin::getAll(false));

        $themes = array_map(function ($theme) {
            return $theme->getDefinition('version');
        }, Theme::getAll());


        try{
            $updates = $api->getAllAvailableUpdates($plugins, $themes);
        }
        catch(\Hawk\HawkApiException $e){
            $updates = array();
        }

        if(!empty($updates)) {
            if(!empty($updates['hawk'])) {
                \Hawk\Plugins\Main\MenuItem::getByName('admin.settings')->label .= View::make(Plugin::current()->getView('available-updates.tpl'), array(
                    'updates' => count($updates['hawk']),
                    'title' => Lang::get('admin.available-updates-title-core', array('number' => count($updates['hawk'])), count($updates['hawk']))
                ));
            }

            if(!empty($updates['plugins'])) {
                \Hawk\Plugins\Main\MenuItem::getByName('admin.plugins')->label .= View::make(Plugin::current()->getView('available-updates.tpl'), array(
                    'updates' => count($updates['plugins']),
                    'title' => Lang::get('admin.available-updates-title-plugins', array('number' => count($updates['plugins'])), count($updates['plugins']))
                ));
            }

            if(!empty($updates['themes'])) {
                \Hawk\Plugins\Main\MenuItem::getByName('admin.themes')->label .= View::make(Plugin::current()->getView('available-updates.tpl'), array(
                    'updates' => count($updates['themes']),
                    'title' => Lang::get('admin.available-updates-title-plugins', array('number' => count($updates['themes'])), count($updates['themes']))
                ));
            }
        }
    }
}