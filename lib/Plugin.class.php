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
	private $name;
	private $rootDir;
	
	private static $mainPlugins = array('main', 'install', 'admin');
	
	/*
	 * public static Plugin getInstanceFromManifest(string $filename, array $data = array())
	 * Create a plugin instance from it manifest and initial data
	 */
	public static function get($name){
		return new Plugin($name);
	}
	
	public static function current(){
		$trace = debug_backtrace()[0]['file'];
		$plugin_dir = dirname($trace);
		$name = basename($plugin_dir);
		
		return new Plugin($name);
	}
	
	/*
	 * public __construct(array $conf)
	 * Create a plugin instance from it configuration
	 */
	public function __construct($name){		
		$this->config = array();
		$this->name = $name;
		$this->rootDir = (in_array($this->name, self::$mainPlugins) ? MAIN_PLUGINS_DIR : PLUGINS_DIR) . '/' . $this->name . '/';
	}
	
	/*
	 * Get the configuration from the database	 
	 */
	public function getConfig(){
		if(!isset($this->config)){					
			$options = DB::get(MAINDB)->select(array(
				'table' => 'Options',
				'condition' => 'plugin = :plugin',
				'binds' => array('plugin' => $plugin),				
			));
			foreach($options as $option){
				$this->config[$option['key']] = $options['value'];
			}
		}
		
		return $this->config;
	}
	
	public function getRootDir(){
		return $this->rootDir;
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
	
	public function isActive(){
		return (bool) DB::get(MAINDB)->select(array(
			'table' => 'Plugin',
			'condition' => 'name = :name',
			'binds' => array('name' => $this->name),
			'fields' => array('active'),
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
}
