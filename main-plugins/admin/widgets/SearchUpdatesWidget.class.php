<?php
/**
 * SearchUpdatesWidget.class.php
 * @author Elvyrra SAS
 */

namespace Hawk\Plugins\Admin;

/**
 * This widget searches for Hawk available updates and display the number of available updates on core and plugins
 */
class SearchUpdatesWidget extends Widget{

    /**
     * Display the available updates
     */
    public function display(){
        // Search for the core update
        $plugins = Plugin::getAll(true);
        $plugins[] = Plugin::get('main');

        foreach($plugins as $plugin){

        }     

    }
}
