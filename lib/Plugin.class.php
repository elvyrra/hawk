<?php
/**
 * Plugin.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class describes the behavior of the application plugins
 * @package Core\Plugin
 */
class Plugin{
	/**
	 * The table in the database where the plugins data are registered
	 */
	const TABLE = 'Plugin';

	/**
	 * The basename of the file containing the plugin definition
	 */
	const MANIFEST_BASENAME = 'manifest.json';

	/**
	 * The plugin name
	 */
	private $name, 

	/**
	 * The plugin definition, described in the file manifest.json, at the root directory of the plugin
	 */
	$definition = array(),


	/**
	 * The plugin optionsn defined in the table Options
	 */
	$options = array();

	/**
	 * The root directory of the plugin
	 */
	private $rootDir,

	/**
	 * Defines if the plugin can be removed or uninstalled
	 */
	$removable,

	/**
	 * Defines the active/inactive state of the plugin
	 */
	$active;
	
	/**
	 * The application main plugins, not removable or editable, used for the application core
	 */
	public static $mainPlugins = array('main', 'install', 'admin');

	/**
	 * The plugin instances
	 */
	private static $instances = array();
	
	/**	 
	 * Constructor
	 * @param string $name The plugin name, corresponding to the directory name
	 * @param array $config The plugin configuration
	 */
	private function __construct($name){
		$this->name = $name;		
		$this->rootDir = ($this->isMainPlugin() ? MAIN_PLUGINS_DIR : PLUGINS_DIR) . $this->name . '/';

		if(!is_dir($this->rootDir)){
			throw new \Exception('The plugin does not exists');
		}
		
		if(!$this->isMainPlugin()){
			if(!is_file($this->rootDir . self::MANIFEST_BASENAME)){
				throw new \Exception('The plugin must have a file manifest.json');
			}
			$this->definition = json_decode(file_get_contents($this->rootDir . self::MANIFEST_BASENAME), true);
		}
		else{
			$this->active = 1;
			$this->removable = false;
			$this->definition = array(
				'title' => Lang::get($this->name . '.plugin-name'),
			);
		}
	}
	
	/**
	 * Get a plugin instance from it name
	 * @param string $name The plugin name to instance
	 * @return Plugin The instance of the wanted plugin
	 */
	public static function get($name){
		try{			
			if(!isset(self::$instances[$name])){				
				self::$instances[$name] = new self($name);
			}

			return self::$instances[$name];
		}
		catch(\Exception $e){
			return null;
		}
	}
	

	/** 
	 * Get the plugin containing the file where this function is called
	 * @return Plugin - The current plugin
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
	  * @param bool $noMain If true, no get the main plugins
	  * @return array The list of plugin instances
	  */
	public static function getAll($noMain = false){
		$plugins = array();
		$dirs = $noMain ? array(PLUGINS_DIR) : array(MAIN_PLUGINS_DIR, PLUGINS_DIR);

		if(Conf::has('db')){
			$configs = DB::get(MAINDB)->select(array(
				'from' => DB::getFullTablename(self::TABLE),
				'index' => 'name',			
				'return' => DB::RETURN_OBJECT
			));
		}
		else{
			$configs = array();
		}

		foreach($dirs as $dir){
			foreach(glob($dir . '*', GLOB_ONLYDIR) as $dir){
				$name = basename($dir);
				$config = isset($configs[$name]) ? $configs[$name] : null;
				
				$plugin = self::get($name);
				$plugin->active = isset($config->active) ? $config->active : false;
				$plugin->removable = isset($config->removable) ? $config->removable : false;
				$plugins[$name] = $plugin;
			}
		}
		
		return $plugins;
	}


	/**
	 * Get all the active plugins
	 * @return array The list of plugin instances
	 */
	public static function getActivePlugins(){
		$configs = DB::get(MAINDB)->select(array(
			'from' => DB::getFullTablename(self::TABLE),
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
	 * @return array - The list of plugin instances
	 */
	public static function getMainPlugins(){
		return array_map(function($name){ 
			return new self($name); 
		}, self::$mainPlugins);
	}
	
	
	
	/** 
	 * Get the plugin name
	 * @return string The plugin name
	 */
	public function getName(){
		return $this->name;
	}


	/**
	 * Check if the plugin is a main plugin
	 * @return boolean True if the plugin is a main plugin (main, install or admin), else False
	 */
	public function isMainPlugin(){
		return in_array($this->name, self::$mainPlugins);
	}

	/**
	 * Get the plugin options
	 * @return array The plugin options, where keys are the options names, and values, the values for each option
	 */
	public function getOptions(){
		if(!isset($this->options)){			
			$this->options = Option::getPluginOptions($this->name);			
		}
		
		return $this->options;
	}


	/**
	 * Get the plugin data from the manifest
	 * @param string $prop If set, the method will return the value of the definition property $prop, else it will return the whole definition array
	 * @return mixed The plugin definition property if $prop is set, or the whoel plugin definition if $prop is not set
	 */
	public function getDefinition($prop = null){
		return $prop ? $this->definition[$prop] : $this->definition;
	}


	/**
	 * Return the root directory of the plugin
	 * @return string the root directory of the plugin
	 */
	public function getRootDir(){
		return $this->rootDir;
	}
	

	/** 
	 * Returns the start file of the plugin. 
	 * The start file is the file start.php, at the root of the plugin directory, that defines the routes, widgets, and event listenter of the plugin.
	 * @return string The file path of the plugin start file
	 */
	public function getStartFile(){
		return $this->getRootDir() . 'start.php';
	}
	

	/**
	 * Returns the directory of the plugin containing the controllers
	 * @return string The directory containing controllers classes of the plugin
	 */
	public function getControllersDir(){
		return $this->rootDir . 'controllers/';
	}
	

	/**
	 * Return the directory containing the plugin models
	 * @return string The directory containing models classes of the plugin
	 */
	public function getModelsDir(){
		return $this->rootDir . 'models/';
	}


	/**
	 * Return the directory containing the plugin widgets
	 * @return string The directory containing the widgets classes of the plugin
	 */
	public function getWidgetsDir(){
		return $this->rootDir . 'widgets/';
	}
	

	/**
	 * Return the directory containing the plugin views
	 * @return string The directory contaning the plugin views
	 */
	public function getViewsDir(){
		return $this->rootDir . 'views/';	
	}
	

	/**
	 * Return the full path of a view in the plugin
	 * @param string $view The basename of the view file to get in the plugin
	 * @return string The full path of the view file
	 */
	public function getView($view){
		// Check if the view is overriden in the current theme
		$file= Theme::getSelected()->getView('plugins/' . $this->name . '/' . $view);
		if(is_file($file)){
			// The view is overriden in the theme
			return $file;
		}

		// The view is not overriden in the view
		return $this->getViewsDir() . $view;
	}
	

	/**
	 * Return the directory containing the plugin static files (js, css, images)
	 * @return string The directory contanining the plugin static files
	 */
	public function getStaticDir(){
		return $this->rootDir . 'static/';
	}
	

	/**
	 * Return the url where to find out the plugin static files
	 * @return string The URL to get the plugin static files
	 */
	public function getStaticUrl(){
		return ROOT_URL . str_replace(ROOT_DIR, '', $this->rootDir) . 'static/';
	}
	

	/**
	 * Return the directory containing the plugin JavaScript files
	 * @return string The directory contaning the plugin JavaScript files
	 */
	public function getJsDir(){
		return $this->getStaticDir() . 'js/';
	}
	

	/**
	 * Return the url where to find out the plugin JavaScript files
	 * @return string The URL of the plugin JavaScript files directory
	 */
	public function getJsUrl(){
		return $this->getStaticUrl() . 'js/';
	}
	

	/**
	 * Return the directory containing the plugin CSS files
	 * @return string The directory contaning the plugin CSS files
	 */
	public function getCssDir(){
		return $this->getStaticDir() . 'css/';
	}
	

	/**
	 * Return the url where to find out the plugin css files
	 * @return string The URL of the plugin CSS files directory
	 */
	public function getCssUrl(){
		return $this->getStaticUrl() . 'css/';
	}
	

	/**
	 * Return the directory containing the plugin language files
	 * @return string The directory contaning the plugin language files
	 */
	public function getLangDir(){
		return $this->rootDir . 'lang/';
	}


	/**
	 * Return the directory containing the plugin files due to user actions
	 * @return string The directory contaning the user files of the plugin
	 */
	public function getUserfilesDir(){
		return USERFILES_PLUGINS_DIR . $this->name . '/';
	}


	/**
	 * Return the directory containing the url to request user files
	 * @return string The URL of the directory containing the user files of the plugin
	 */
	public function getUserfilesUrl(){
		return USERFILES_PLUGINS_URL . $this->name . '/';
	}
	

	/**
	 * Check if the plugin is installed. The plugin is installed if it appears in the database
	 * @return boolean True if the plugin is installed, False else
	 */
	public function isInstalled(){
		return (bool) DB::get(MAINDB)->count(DB::getFullTablename(self::TABLE), 'name = :name', array('name' => $this->name));
	}
	

	/**
	 * Get the namespace used for all files in the plugin. The namespace is generated from the plugin name
	 * @return string The plugin namespace
	 */
	public function getNamespace(){
		$namespace = preg_replace_callback('/(^|\-)(\w?)/', function($m){
            return strtoupper($m[2]);                    
		}, $this->name);  

		return 'Hawk\\Plugins\\' . $namespace;
	}

	/**
	 * Instance the plugin installer
	 * @return PluginInstaller The instance of the plugin installer
	 */
	public function getInstallerInstance(){
		if(isset($this->manager)){
			return $this->manager;
		}

		$class = $this->getNamespace() . '\\Installer';
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
		DB::get(MAINDB)->insert(DB::getFullTablename(self::TABLE), array(
			'name' => $this->name,			
			'active' => 0
		), 'IGNORE');

		try{
			$this->getInstallerInstance()->install();		
			Log::notice('The plugin ' . $this->name . ' has been installed');
		}
		catch(\Exception $e){
			DB::get(MAINDB)->delete(DB::getFullTablename(self::TABLE), new DBExample(array('name' => $this->name)));

			Log::error('En error occured while installing plugin ' . $this->name . ' : ' . $e->getMessage());
			throw $e;
		}
	}
	

	/**
	 * Uninstall the plugin
	 */
	public function uninstall(){
		Db::get(MAINDB)->delete(DB::getFullTablename(self::TABLE), new DBExample(array('name' => $this->name)));

		try{
			$this->getInstallerInstance()->uninstall();
			Log::notice('The plugin ' . $this->name . ' has been uninstalled');
		}
		catch(\Exception $e){
			DB::get(MAINDB)->insert(DB::getFullTablename(self::TABLE), array(
				'name' => $this->name,			
				'active' => 0
			), 'IGNORE');

			Log::error('En error occured while uninstalling plugin ' . $this->name . ' : ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Check if the plugin is active
	 * @return boolean True if the plugin is active, False else
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
		DB::get(MAINDB)->update(DB::getFullTablename(self::TABLE), new DBExample(array('name' => $this->name)), array('active' => 1));	

		try{
			$this->getInstallerInstance()->activate();
			Log::notice('The plugin ' . $this->name . ' has been activated');
		}
		catch(\Exception $e){
			DB::get(MAINDB)->update(DB::getFullTablename(self::TABLE), new DBExample(array('name' => $this->name)), array('active' => 0));
			
			Log::error('En error occured while activating plugin ' . $this->name . ' : ' . $e->getMessage());
			throw $e;
		}
	}
	

	/**
	 * Deactive the plugin
	 */
	public function deactivate(){
		// Deactivate the plugin
		$this->active = 0;
		DB::get(MAINDB)->update(DB::getFullTablename(self::TABLE), new DBExample(array('name' => $this->name)), array('active' => 0));	

		try{
			$this->getInstallerInstance()->deactivate();
			Log::notice('The plugin ' . $this->name . ' has been deactivated');
		}
		catch(\Exception $e){
			DB::get(MAINDB)->update(DB::getFullTablename(self::TABLE), new DBExample(array('name' => $this->name)), array('active' => 1));
			
			Log::error('En error occured while deactivating plugin ' . $this->name . ' : ' . $e->getMessage());
			throw $e;
		}
	}


	/**
	 * Search for available updates
	 * @return string the last available version on the Hawk Platform
	 */
	public function searchLastVersion(){
		// Call the Hawk API to get the last version of the plugin
		$request = new HTTPRequest(array(
			'url' => HAWK_API_BASE_URL . 'plugins/' . $this->name . '/versions/last',
			'method' => HTTPRequest::METHOD_GET,
			'dataType' => 'json'
		));

		$request->send();

		if($request->getStatusCode() !== 200){
			return null;
		}
		else{
			$body = $request->getResponse();

			return $body['version'];
		}
	}


	/**
	 * Check of the plugin as an available update
	 * @return boolean TRUE if the plugin is updatable, else FALSE
	 */
	public function isUpdatable(){
		$lastVersion = $this->searchLastVersion();
		return $lastVersion && $lastVersion > $this->getDefinition('version');
	}

	/**
	 * Update the plugin
	 */
	public function update(){
		if($this->isUpdatable()){
			// The plugin is updatable, download the updated files
			
		}
	}
}
