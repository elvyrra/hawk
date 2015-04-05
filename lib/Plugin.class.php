<?php
/**********************************************************************
 *    						Plugin.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 * Description : This class is used to manage the plugins
 *
 **********************************************************************/
class Plugin{
	const TABLE = "Plugin";
	private $name;
	private $rootDir;
	
	private static $mainPlugins = array('main', 'install', 'admin');
	
	/*	 
	 * Create a plugin instance from it configuration
	 * @param {String} $name - The plugin name, correspongin to the directory name
	 */
	private function __construct($name){		
		$this->config = array();
		$this->name = $name;
		$this->rootDir = (in_array($this->name, self::$mainPlugins) ? MAIN_PLUGINS_DIR : PLUGINS_DIR) . '/' . $this->name . '/';
	}
	
	/*
	 * public static Plugin getInstanceFromManifest(string $filename, array $data = array())
	 * Create a plugin instance from it manifest and initial data
	 */
	public static function get($name){
		return new Plugin($name);
	}
	
	public static function current(){
		$trace = debug_backtrace()[0]['file'];
		if(strpos($trace, PLUGINS_DIR) !== false){
			$dir = str_replace(PLUGINS_DIR, '', $trace);
		}
		elseif(strpos($trace, MAIN_PLUGINS_DIR) !== false){
			$dir = str_replace(MAIN_PLUGINS_DIR, '', $trace);
		}
		else{
			return null;
		}
		list($name) = explode(DIRECTORY_SEPARATOR, $dir);		
		
		return new Plugin($name);
	}
	
	public static function getAll(){
		$plugins = array();
		foreach(array(MAIN_PLUGINS_DIR, PLUGINS_DIR) as $dir){
			foreach(glob($dir . '*') as $name){
				$plugins[] = new Plugin($name);
			}
		}
		
		return $plugins;
	}
	
	public static function getActivePlugins(){
		return DB::get(MAINDB)->select(array(
			'from' => self::TABLE,
			'where' => 'active = 1',
			'return' => __CLASS__,
		));
	}
	
	public static function getMainPlugins(){
		return array_map(function($name){ return new self($name); }, self::$mainPlugins);
	}
	
	
	
	/*
	 * Get the configuration from the database	 
	 */
	public function getConfig(){
		if(!isset($this->config)){								
			$this->config = Option::getPluginOptions($this->name);			
		}
		
		return $this->config;
	}
	
	public function getRootDir(){
		return $this->rootDir;
	}
	
	public function getStartFile(){
		return $this->getRootDir() . 'start.php';
	}
	
	public function getControllersDir(){
		return $this->rootDir . 'controllers/';
	}
	
	public function getModelsDir(){
		return $this->rootDir . 'models/';
	}

	public function getWidgetsDir(){
		return $this->rootDir . 'widgets/';
	}
	
	public function getViewsDir(){
		return $this->rootDir . 'views/';	
	}
	
	public function getView($view){
		return $this->getViewsDir() . $view;
	}
	
	public function getStaticDir(){
		return $this->rootDir . 'static/';
	}
	
	public function getStaticUrl(){
		return '/' . str_replace(ROOT_DIR, '', $this->rootDir) . 'static/';
	}
	
	public function getJsDir(){
		return $this->getStaticDir() . 'js/';
	}
	
	public function getJsUrl(){
		return $this->getStaticUrl() . 'js/';
	}
	
	public function getCssDir(){
		return $this->getStaticDir() . 'css/';
	}
	
	public function getCssUrl(){
		return $this->getStaticUrl() . 'css/';
	}
	
	public function getLangDir(){
		return $this->rootDir . 'lang/';
	}
	
	public function isInstalled(){
		return (bool) DB::get(MAINDB)->count(self::TABLE, 'name = :name', array('name' => $this->name));
	}
	
	public function isActive(){
		return (bool) DB::get(MAINDB)->select(array(
			'fields' => array('active'),
			'from' => 'Plugin',
			'where' => 'name = :name',
			'binds' => array('name' => $this->name),
			'return' => DB::RETURN_OBJECT,
			'one' => true
		))->active;
	}
	
	public function activate(){
		// Activate the plugin
		DB::get(MAINDB)->update("Plugin", 'name = :name', array('active' => 1), array('name' => $this->name));	
	}
	
	public function deactivate(){
		// Deactivate the plugin
		DB::get(MAINDB)->update("Plugin", 'name = :name', array('active' => 0), array('name' => $this->name));	
	}
	
	public function install(){
		DB::get(MAINDB)->insert('Plugin', array(
			'name' => $this->name,			
			'active' => 0
		), 'IGNORE');
	}
	
	public function uninstall(){
		Db::get(MAINDB)->delete("Plugin", 'name = :name', array('name' => $this->name));
	}
	
	public function importLanguageFiles(){
		foreach(glob($this->getLangDir().'*') as $file){
			Language::importFile($file);
		}		
	}
}
