<?php
/**
 * SearchUpdateWidget.class.php
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
            $coreUpdates = $api->getCoreAvailableUpdates();
        }
        catch(\Hawk\HawkApiException $e){
            $coreUpdates = array();
        }

        if(count($coreUpdates)){
            $updates += count($coreUpdates);
            $titles[] = Lang::get('admin.available-updates-title-core', array('number' =>count($coreUpdates)), count($coreUpdates));
        }


        // Get the available updates for the plugins
        $plugins = Plugin::getAll(true);
        $plugins = array_map(function($plugin){
            return $plugin->getDefinition('version');
        }, $plugins);

        try{
            $pluginsUpdates = $api->getPluginsAvailableUpdates($plugins);
        }
        catch(\Hawk\HawkApiException $e){
            $pluginsUpdates = array();
        }
        if(count($pluginsUpdates)){
            $updates += count($pluginsUpdates);
            $titles[] = Lang::get('admin.available-updates-title-plugins', array('number' => count($pluginsUpdates)), count($pluginsUpdates));
        }

        // Get the available updates on themes
        $themes = Plugin::getAll();
        $themes = array_map(function($theme){
            return $theme->getDefinition('version');
        }, $themes);

        try{
            $themesUpdates = $api->getThemesAvailableUpdates($themes);
        }
        catch(\Hawk\HawkApiException $e){
            $themesUpdates = array();
        }
        if(count($themesUpdates)){
            $updates += count($themesUpdates);
            $titles[] = Lang::get('admin.available-updates-title-plugins', array('number' => count($themesUpdates)), count($themesUpdates));
        }

        return View::make(Plugin::current()->getView('available-updates.tpl'), array(
            'updates' => $updates,
            'title' => implode(', ', $titles)
        ));
    }
}