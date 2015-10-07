<?php
/**
 * SearchUpdates.class.php
 */

namespace Hawk\Plugins\Main;

/**
 * This class search updates for Hawk core, plugins, and themes
 */
class SearchUpdates{
	/**
	 * Search updates for Hawk core
	 * @return array The available version data, or false if no update is available
	 */
	public static function coreUpdates(){
		$currentVersion = file_get_contents(ROOT_DIR . 'version.txt');

		$request = new HTTPRequest(array(
			'url' => HAWK_API_BASE_URL . '/hawk/updates',
			'method' => 'post',
			'contentType' => 'json',
			'dataType' => 'json',
			'body' => array('version' => $currentVersion)
		));

		$request->send();

		if($request->getStatusCode() == 200){
			$result = $request->getResponse();

			if(!empty($result) && $result['version'] > $currentVersion){
				return $result;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}
}