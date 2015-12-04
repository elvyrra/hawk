<?php
/**
 * SearchUpdateWidget.php
 */

namespace Hawk\Plugins\Admin;

/**
 * This widget displays in the main menu a little div with the number of available updates
 */
class SearchUpdatesWidget extends Widget{
    
    public function display(){

        // The number of updates
        $updates = 0;
        $titles = array();

        $api = new HawkApi;

        // Get the available updates on Hawk
        try{
            $coreUpdates = count($api->getCoreAvailableUpdates());
        }
        catch(\Hawk\HawkApiException $e){
            $coreUpdates = 0;
        }

        if($coreUpdates){
            \Hawk\Plugins\Main\MenuItem::getByName('admin.settings')->label .= View::make(Plugin::current()->getView('available-updates.tpl'), array(
                'updates' => $coreUpdates,
                'title' => Lang::get('admin.available-updates-title-core', array('number' =>$coreUpdates), $coreUpdates)
            ));
        }


        // Get the available updates for the plugins
        $plugins = array_map(function($plugin){
            return $plugin->getDefinition('version');
        }, Plugin::getAll(true));

        try{
            $pluginsUpdates = count($api->getPluginsAvailableUpdates($plugins));
        }
        catch(\Hawk\HawkApiException $e){
            $pluginsUpdates = 0;
        }

        if($pluginsUpdates){
            \Hawk\Plugins\Main\MenuItem::getByName('admin.plugins')->label .= View::make(Plugin::current()->getView('available-updates.tpl'), array(
                'updates' => $pluginsUpdates,
                'title' => Lang::get('admin.available-updates-title-plugins', array('number' => $pluginsUpdates), $pluginsUpdates)
            ));
        }

        // Get the available updates on themes
        $themes = Plugin::getAll();
        $themes = array_map(function($theme){
            return $theme->getDefinition('version');
        }, $themes);

        try{
            $themesUpdates = count($api->getThemesAvailableUpdates($themes));
        }
        catch(\Hawk\HawkApiException $e){
            $themesUpdates = 0;
        }
        if($themesUpdates){
            \Hawk\Plugins\Main\MenuItem::getByName('admin.themes')->label .= View::make(Plugin::current()->getView('available-updates.tpl'), array(
                'updates' => $themesUpdates,
                'title' => Lang::get('admin.available-updates-title-plugins', array('number' => $themesUpdates), $themesUpdates)
            ));            
        }
    }
}