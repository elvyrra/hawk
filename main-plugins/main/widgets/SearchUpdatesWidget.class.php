<?php
/**
 * SearchUpdateWidget.class.php
 */

namespace Hawk\Plugins\Main;

/**
 * This widget displays in the main menu a little div with the number of available updates
 */
class SearchUpdatesWidget extends Widget{
	
	public function display(){
		// The number of updates
		$updates = 0;
		$titles = array();

		$api = new HawkApi;
		$coreUpdates = $api->getCoreUpdates();
		if(count($coreUpdates)){
			$updates ++;
			$titles[] = Lang::get('main.available-updates-title-core');
		}

		return View::make(Plugin::current()->getView('available-updates.tpl'), array(
			'updates' => $updates,
			'title' => implode(', ', $titles)
		));
	}
}