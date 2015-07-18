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
	private $name, 
			$definition = array(),
			$config = array(),
			$options = array();
	private $rootDir,
			$removable,
			$active;
	
	
	private static $mainPlugins = array('main', 'install', 'admin');
	private static $instances = array();
	
	/*	 
	 * Create a plugin instance from it configuration
	 * @param {String} $name - The plugin name, correspongin to the directory name
	 */
	private function __construct($name, $config = array()){
		$this->name = $name;
		if(is_array($config)){
			foreach($config as $key => $value){
				$this->$key = $value;
			}
		}
		$this->rootDir = ($this->isMainPlugin() ? MAIN_PLUGINS_DIR : PLUGINS_DIR) . $this->name . '/';
		
		if(!$this->isMainPlugin()){
			$this->definition = json_decode(file_get_contents($this->rootDir . 'manifest.json'), true);

			if(!$this->definition['installer']){
				throw new Exception("The plugin $this->name must have a installer filled in it manifest");
			}
		}
		else{
			$this->active = 1;
			$this->removable = false;
		}
	}
	
	/**
	 * Create a plugin instance from it name
	 * @param {string} $name - the name of the plugin, and the directory plugins dir
	 * @param {array} $config - the plugin configuration in the database
	 * @return {Plugin} - the instance of the wanted plugin
	 */
	public static function get($name, $config= array()){
		try{			
			if(!isset(self::$instances[$name])){				
				self::$instances[$name] = new self($name, $config);
			}

			return self::$instances[$name];
		}
		catch(Exception $e){
			return null;
		}
	}
	

	/** 
	 * Get the plugin containing the file where this function was called
	 * @return {Plugin} - The current plugin
	 */
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
		
		return self::get($name);
	}
	

	/** 
	  * get all the plugins
	  * @param {bool} $noMain - if true, no get the main plugins
	  * @return {Array} - The list of plugin instances
	  */
	public static function getAll($noMain = false){
		$plugins = array();
		$dirs = $noMain ? array(PLUGINS_DIR) : array(MAIN_PLUGINS_DIR, PLUGINS_DIR);

		$configs = DB::get(MAINDB)->select(array(
			'from' => self::TABLE,
			'index' => 'name',			
		));

		foreach($dirs as $dir){
			foreach(glob($dir . '*') as $dir){
				$name = basename($dir);
				$config = isset($configs[$name]) ? $configs[$name] : array();
				$plugins[$name] = self::get($name, $config);
			}
		}
		
		return $plugins;
	}


	/**
	 * Get all the active plugins
	 * @return {Array} - The list of plugin instances
	 */
	public static function getActivePlugins(){
		$configs = DB::get(MAINDB)->select(array(
			'from' => self::TABLE,
			'where' => 'active = 1',						
		));

		$plugins = array();
		foreach($configs as $config){
			$plugins[$config['name']] = self::get($config['name'], $config);
		}

		return $plugins;
	}
	

	/**
	 * Get the main plugins
	 * @return {Array} - The list of plugin instances
	 */
	public static function getMainPlugins(){
		return array_map(function($name){ return new self($name); }, self::$mainPlugins);
	}
	
	
	
	/** 
	 * get the plugin name
	 */
	public function getName(){
		return $this->name;
	}


	/**
	 * Check if the plugin is a main plugin
	 */
	public function isMainPlugin(){
		return in_array($this->name, self::$mainPlugins);
	}

	/*
	 * Get the configuration from the database	 
	 */
	public function getOptions(){
		if(!isset($this->options)){			
			$this->options = Option::getPluginOptions($this->name);			
		}
		
		return $this->options;
	}


	/**
	 * Get the plugin data from the manifest
	 */
	public function getDefinition($prop = null){
		return $prop ? $this->definition[$prop] : $this->definition;
	}


	/**
	 * Return the root directory of the plugin
	 */
	public function getRootDir(){
		return $this->rootDir;
	}
	

	/** 
	 * Returns the start file of the plugin
	 */
	public function getStartFile(){
		return $this->getRootDir() . 'start.php';
	}
	

	/**
	 * Returns the directory of the plugin containing the controllers
	 */
	public function getControllersDir(){
		return $this->rootDir . 'controllers/';
	}
	

	/**
	 * Return the directory containing the plugin models
	 */
	public function getModelsDir(){
		return $this->rootDir . 'models/';
	}


	/**
	 * Return the directory containing the plugin widgets
	 */
	public function getWidgetsDir(){
		return $this->rootDir . 'widgets/';
	}
	

	/**
	 * Return the directory containing the plugin views
	 */
	public function getViewsDir(){
		return $this->rootDir . 'views/';	
	}
	

	/**
	 * Return the full path of a view in the plugin
	 */
	public function getView($view){
		// Check if the view is overriden in the current theme
		$file= ThemeManager::getSelected()->getView('plugins/' . $this->name . '/' . $view);
		if(is_file($file)){
			// The view is overriden in the theme
			return $file;
		}

		// The view is not overriden in the view
		return $this->getViewsDir() . $view;
	}
	

	/**
	 * Return the directory containing the plugin static files (js, css, images)
	 */
	public function getStaticDir(){
		return $this->rootDir . 'static/';
	}
	

	/**
	 * Return the url where to find out the plugin static files
	 */
	public function getStaticUrl(){
		return ROOT_URL . str_replace(ROOT_DIR, '', $this->rootDir) . 'static/';
	}
	

	/**
	 * Return the directory containing the plugin js files
	 */
	public function getJsDir(){
		return $this->getStaticDir() . 'js/';
	}
	

	/**
	 * Return the url where to find out the plugin js files
	 */
	public function getJsUrl(){
		return $this->getStaticUrl() . 'js/';
	}
	

	/**
	 * Return the directory containing the plugin css files
	 */
	public function getCssDir(){
		return $this->getStaticDir() . 'css/';
	}
	

	/**
	 * Return the url where to find out the plugin css files
	 */
	public function getCssUrl(){
		return $this->getStaticUrl() . 'css/';
	}
	

	/**
	 * Return the directory containing the plugin language files
	 */
	public function getLangDir(){
		return $this->rootDir . 'lang/';
	}


	/**
	 * Return the directory containing the plugin files due to user (uploads)
	 */
	public function getUserfilesDir(){
		return USERFILES_PLUGINS_DIR . $this->name . '/';
	}


	/**
	 * Return the directory containing the url to request user files
	 */
	public function getUserfilesUrl(){
		return USERFILES_PLUGINS_URL . $this->name . '/';
	}
	

	/**
	 * Check if the plugin is installed. The plugin is installed if it appears in the database
	 */
	public function isInstalled(){
		return (bool) DB::get(MAINDB)->count(self::TABLE, 'name = :name', array('name' => $this->name));
	}
	

	public function getInstallerInstance(){
		if(isset($this->manager)){
			return $this->manager;
		}

		$class = $this->getDefinition('installer');
		if(!empty($class)){
			$this->manager = new $class($this);
			return $this->manager;
		}
		else{
			return null;
		}
	}

	/**
	 * Install the plugin
	 */
	public function install(){
		DB::get(MAINDB)->insert('Plugin', array(
			'name' => $this->name,			
			'active' => 0
		), 'IGNORE');

		$this->getInstallerInstance()->install();		
	}
	

	/**
	 * Uninstall the plugin
	 */
	public function uninstall(){
		Db::get(MAINDB)->delete("Plugin", 'name = :name', array('name' => $this->name));

		$this->getInstallerInstance()->uninstall();
	}

	/**
	 * Check if the plugin is active
	 */
	public function isActive(){
		return $this->active;
	}
	

	/**
	 * Activate the plugin in the database
	 */
	public function activate(){
		// Activate the plugin
		$this->active = 1;
		DB::get(MAINDB)->update("Plugin", 'name = :name', array('active' => 1), array('name' => $this->name));	

		$this->getInstallerInstance()->activate();
	}
	

	/**
	 * Deactive the plugin
	 */
	public function deactivate(){
		// Deactivate the plugin
		$this->active = 0;
		DB::get(MAINDB)->update("Plugin", 'name = :name', array('active' => 0), array('name' => $this->name));	

		$this->getInstallerInstance()->deactivate();
	}
	

	/**
	 * Import the language files of the plugin in the database
	 */
	public function importLanguageFiles(){
		foreach(glob($this->getLangDir().'*') as $file){
			Language::importFile($file);
		}		
	}
}
