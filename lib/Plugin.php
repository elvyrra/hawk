<?php
/**
 * Plugin.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the behavior of the application plugins
 *
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
     * The pattern for a plugin name
     */
    const NAME_PATTERN = '[a-zA-Z0-9\-_.]+';

    /**
     * The plugin name
     *
     * @var strign
     */
    private $name,

    /**
     * The plugin definition, described in the file manifest.json, at the root directory of the plugin
     *
     * @var array
     */
    $definition = array(),


    /**
     * The plugin optionsn defined in the table Options
     *
     * @var array
     */
    $options = array();

    /**
     * The root directory of the plugin
     *
     * @var string
     */
    private $rootDir,

    /**
     * Defines if the plugin can be removed or uninstalled
     *
     * @var boolean
     */
    $removable = true,

    /**
     * Defines the active/inactive state of the plugin
     *
     * @var boolean
     */
    $active;


    /**
     * The application main plugins, not removable or editable, used for the application core
     *
     * @var array
     */
    public static $mainPlugins = array('main', 'install', 'admin');


    /**
     * The plugin instances
     *
     * @var array
     */
    public static $instances = array();


    /**
     * Forbidden plugin names
     *
     * @var array
     */
    public static $forbiddenNames = array('custom');


    /**
     * Cache array containing the plugins instances references for files.
     * This is used to increase performances when calling Plugin::current() or Plugin::getFilePlugin($file)
     *
     * @var array
     */
    private static $filePlugins = array();


    /**
     * Constructor
     *
     * @param string $name The plugin name, corresponding to the directory name
     */
    private function __construct($name){
        $this->name = $name;
        $this->rootDir = PLUGINS_DIR . $this->name . '/';

        if(!is_dir($this->rootDir)) {
            throw new \Exception('The plugin does not exists');
        }

        if(!$this->isMainPlugin()) {
            if(!is_file($this->rootDir . self::MANIFEST_BASENAME)) {
                throw new \Exception('The plugin must have a file manifest.json');
            }
            $this->definition = json_decode(file_get_contents($this->rootDir . self::MANIFEST_BASENAME), true);
        }
        else{
            $this->active = true;
            $this->removable = false;
            $this->definition = array(
                'title' => Lang::get($this->name . '.plugin-name'),
            );
        }
    }


    /**
     * Get a plugin instance from it name
     *
     * @param string $name The plugin name to instance
     *
     * @return Plugin The instance of the wanted plugin
     */
    public static function get($name){
        try{
            if(!isset(self::$instances[$name])) {
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
     *
     * @return Plugin The current plugin
     */
    public static function current(){
        $callingFile = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

        return self::getFilePlugin($callingFile);
    }


    /**
     * Get the plugin contaning a given filename
     *
     * @param string $file The filename to get the containing plugin of
     *
     * @return Plugin The found plugin
     */
    public static function getFilePlugin($file){
        // Search plugin in cache
        if(isset(self::$filePlugins[$file])) {
            return self::$filePlugins[$file];
        }

        if(strpos($file, PLUGINS_DIR) !== false) {
            $dir = str_replace(PLUGINS_DIR, '', $file);
        }
        else{
            return null;
        }
        list($name) = explode(DIRECTORY_SEPARATOR, $dir);

        // instanciate the plugin
        $plugin = self::get($name);

        // Register the pluygin in the memory cache
        self::$filePlugins[$file] = &$plugin;

        // return the plugin instance
        return $plugin;
    }


    /**
      * Get all the plugins
     *
      * @param bool $includeMain If true, include main plugins to the returned list
      * @param bool $loadConf    If set to true, load the plugins conf in the database
      *
      * @return array The list of plugin instances
      */
    public static function getAll($includeMain = true, $loadConf = false){
        $plugins = array();

        if($loadConf && App::conf()->has('db')) {
            $configs = PluginModel::getAll(PluginModel::getPrimaryColumn());
        }
        else{
            $configs = array();
        }

        foreach(glob(PLUGINS_DIR . '*', GLOB_ONLYDIR) as $dir) {
            $name = basename($dir);
            if(!$includeMain && in_array($name, self::$mainPlugins)) {
                continue;
            }

            $config = isset($configs[$name]) ? $configs[$name] : null;

            $plugin = self::get($name);
            if($loadConf && !$plugin->isMainPlugin()) {
                $plugin->active = isset($config->active) ? (bool) $config->active : false;
            }
            $plugins[$name] = $plugin;
        }

        return $plugins;
    }


    /**
     * Get all the active plugins
     *
     * @param bool $includeMain If set to true, include main plugins in the returned array
     *
     * @return array The list of plugin instances
     */
    public static function getActivePlugins($includeMain = true){
        $plugins = self::getAll($includeMain, true);

        return array_filter($plugins, function ($plugin) {
            return $plugin->active;
        });
    }


    /**
     * Get the main plugins
     *
     * @return array The list of plugin instances
     */
    public static function getMainPlugins(){
        return array_map(function ($name) {
            return new self($name);
        }, self::$mainPlugins);
    }



    /**
     * Get the plugin name
     *
     * @return string The plugin name
     */
    public function getName(){
        return $this->name;
    }


    /**
     * Check if the plugin is a main plugin
     *
     * @return boolean True if the plugin is a main plugin (main, install or admin), else False
     */
    public function isMainPlugin(){
        return in_array($this->name, self::$mainPlugins);
    }

    /**
     * Get the plugin options
     *
     * @return array The plugin options, where keys are the options names, and values, the values for each option
     */
    public function getOptions(){
        if(!isset($this->options)) {
            $this->options = Option::getPluginOptions($this->name);
        }

        return $this->options;
    }


    /**
     * Get the plugin data from the manifest
     *
     * @param string $prop If set, the method will return the value of the definition property $prop.
     *                      If not set, it will return the whole definition array
     *
     * @return mixed The plugin definition property if $prop is set,
     *                   or the whole plugin definition if $prop is not set
     */
    public function getDefinition($prop = null){
        if($prop) {
            return isset($this->definition[$prop]) ? $this->definition[$prop]: null;
        }
        return $this->definition;
    }


    /**
     * Return the root directory of the plugin
     *
     * @return string the root directory of the plugin
     */
    public function getRootDir(){
        return $this->rootDir;
    }


    /**
     * Returns the start file of the plugin.
     * The start file is the file start.php, at the root of the plugin directory,
     * that defines the routes, widgets, and event listenter of the plugin.
     *
     * @return string The file path of the plugin start file
     */
    public function getStartFile(){
        return $this->getRootDir() . 'start.php';
    }


    /**
     * Start the plugin
     */
    public function start() {
        if(is_file($this->getStartFile())) {
            include $this->getStartFile();
        }
    }


    /**
     * Return the readme file of the plugin.
     * The readme file is the file README.md, written in markdown, that contains the complete description
     * of the plugin
     */
    public function getReadmeFile() {
        return $this->getRootDir() . 'README.md';
    }


    /**
     * Returns the directory of the plugin containing the controllers
     *
     * @return string The directory containing controllers classes of the plugin
     */
    public function getControllersDir(){
        return $this->getRootDir() . 'controllers/';
    }


    /**
     * Return the directory containing the plugin language files
     *
     * @return string The directory contaning the plugin language files
     */
    public function getLangDir(){
        return $this->getRootDir() . 'lang/';
    }

    /**
     * Return the directory containing the plugin models
     *
     * @return string The directory containing models classes of the plugin
     */
    public function getModelsDir(){
        return $this->getRootDir() . 'models/';
    }


    /**
     * Return the directory containing the plugin widgets
     *
     * @return string The directory containing the widgets classes of the plugin
     */
    public function getWidgetsDir(){
        return $this->getRootDir() . 'widgets/';
    }


    /**
     * Return the directory containing the plugin views
     *
     * @return string The directory contaning the plugin views
     */
    public function getViewsDir(){
        return $this->getRootDir() . 'views/';
    }


    /**
     * Return the full path of a view in the plugin
     *
     * @param string $view The basename of the view file to get in the plugin
     *
     * @return string The full path of the view file
     */
    public function getView($view){
        // Check if the view is overriden in the current theme
        $file= Theme::getSelected()->getView('plugins/' . $this->name . '/' . $view);
        if(is_file($file)) {
            // The view is overriden in the theme
            return $file;
        }

        // The view is not overriden in the view
        return $this->getViewsDir() . $view;
    }


    /**
     * Return the directory containing the plugin static files (js, css, images)
     *
     * @return string The directory path
     */
    public function getStaticDir(){
        return $this->getRootDir() . 'static/';
    }

    /**
     * Return the directory containing the plugin public static files (acessible by HTTP requests)
     *
     * @return string The directory path
     */
    public function getPublicStaticDir(){
        return STATIC_PLUGINS_DIR . $this->name . '/';
    }


    /**
     * Return the url of a static file in the plugin, or the directory containing static files if $basename is empty
     *
     * @param string $basename The file to get the URL of
     *
     * @return string The URL
     */
    public function getStaticUrl($basename = '') {
        $version = $this->isMainPlugin() ? HAWK_VERSION : $this->getDefinition('version');
        $baseUrl = PLUGINS_ROOT_URL . $this->name . '/' . $version . '/';
        if(empty($basename)) {
            return $baseUrl;
        }
        else{
            $privateFilename = $this->getStaticDir() . $basename;
            $publicFilename = $this->getPublicStaticDir() . $basename;

            if(is_file($privateFilename) && (!is_file($publicFilename) || filemtime($privateFilename) > filemtime($publicFilename))) {
                if(!is_dir(dirname($publicFilename))) {
                    mkdir(dirname($publicFilename), 0755, true);
                }

                copy($privateFilename, $publicFilename);
            }

            return $baseUrl . $basename;
        }
    }





    /**
     * Return the directory containing the plugin JavaScript files
     *
     * @return string The directory path
     */
    public function getJsDir(){
        return $this->getStaticDir() . 'js/';
    }


    /**
     * Return the directory containing the plugin public JavaScript files (accessible by HTTP requests)
     *
     * @return string The directory path
     */
    public function getPublicJsDir(){
        return $this->getPublicStaticDir() . 'js/';
    }

    /**
     * Return the URL of a public javascript file,
     * or the URL of the directory containing public javascript files if $basename is empty
     *
     * @param string $basename The Javascript file basename
     *
     * @return string The URL
     */
    public function getJsUrl($basename = ''){
        if(empty($basename)) {
            return $this->getStaticUrl() . 'js/';
        }
        else {
            // The characater '?' at the end is added as a work around for require
            return $this->getStaticUrl('js/' . $basename) . '?';
        }
    }






    /**
     * Return the directory containing the plugin CSS files
     *
     * @return string The directory contaning the plugin CSS files
     */
    public function getLessDir(){
        return $this->getStaticDir() . 'less/';
    }


    /**
     * Return the directory containing the plugin public CSS files (accessible by HTTP requests)
     *
     * @return string The directory path
     */
    public function getPublicCssDir(){
        return $this->getPublicStaticDir() . 'css/';
    }


    /**
     * Return the URL of a public CSS file,
     * or the URL of the directory containing public CSS files if $basename is empty
     *
     * @param string $basename The Less file basename
     *
     * @return string The URL
     */
    public function getCssUrl($basename = ""){
        $cssUrl = $this->getStaticUrl() . 'css/';
        if(empty($basename)) {
            return $cssUrl;
        }
        else{
            $privateFilename = $this->getLessDir() . $basename;
            $cssBasename = preg_replace('/\.less$/', '.css', $basename);
            $publicFilename = $this->getPublicCssDir() . $cssBasename;

            if(is_file($privateFilename)) {

                Event::on('built-less', function (Event $event) use ($privateFilename) {
                    if($event->getData('source') === $privateFilename) {
                        // Copy all static files except less and JS
                        foreach(glob($this->getStaticDir() . '*') as $elt){
                            if(! in_array(basename($elt), array('less', 'js'))) {
                                App::fs()->copy($elt, $this->getPublicStaticDir());
                            }
                        }
                    }
                });
                Less::compile($privateFilename, $publicFilename);
            }

            return $cssUrl . $cssBasename . '?' . filemtime($publicFilename);
        }
    }




    /**
     * Return the directory containing the plugin files due to user actions
     *
     * @return string The directory contaning the user files of the plugin
     */
    public function getUserfilesDir(){
        return USERFILES_PLUGINS_DIR . $this->name . '/';
    }


    /**
     * Return the full path of a plugin private userfile
     *
     * @param string $filename The filename, relative to the userfiles directory
     *
     * @return string
     */
    public function getUserfile($filename) {
        return $this->getUserfilesDir() . $filename;
    }


    /**
     * Return the directory containing the public (accessible by HTTP requests) plugin files due to user actions
     *
     * @return string The directory contaning the user files of the plugin
     */
    public function getPublicUserfilesDir(){
        return $this->getPublicStaticDir() . 'userfiles/';
    }


    /**
     * Return the URL of a static userfile,
     * or the URL of the directory contaning the userfiles, if $basename is empty
     *
     * @param string $basename The basename of the file to get the access URL
     *
     * @return string The URL
     */
    public function getUserfilesUrl($basename = ''){
        $baseUrl = $this->getStaticUrl() . 'userfiles/';
        if(empty($basename)) {
            return $baseUrl;
        }
        else{
            return $baseUrl . $basename . '?' . filemtime($this->getPublicUserfilesDir() . $basename);
        }
    }


    /**
     * Get the folder containing the plugin img
     *
     * @return string The directory path
     */
    public function getImgDir() {
        return $this->getStaticDir() . 'img/';
    }


    /**
     * Return the directory containing the plugin public img files
     *
     * @return string The directory path
     */
    public function getPublicImgDir(){
        return $this->getPublicStaticDir() . 'img/';
    }


    /**
     * Get the URL of an image, or the url of the image directory
     *
     * @param string $basename The image file basename
     *
     * @return string The URL
     */
    public function getImgUrl($basename = '') {
        if(empty($basename)) {
            return $this->getStaticUrl() . 'img/';
        }
        else{
            return $this->getStaticUrl('img/' . $basename);
        }
    }


    /**
     * Get the plugin logo URL
     *
     * @return string The logo url
     */
    public function getLogoUrl() {
        if($this->getDefinition('logo')) {
            return $this->getImgUrl($this->getDefinition('logo'));
        }
        return null;
    }


    /**
     * This method get the favicon file of the plugin, convert it to a valid .ico file, copy it in the static directory
     * and returns the URL of the created .ico file
     *
     * @return string the plugin favicon url
     */
    public function getFaviconUrl() {
        $file = $this->getDefinition('favicon') ? $this->getDefinition('favicon') : $this->getDefinition('logo');
        if(!$file) {
            return null;
        }

        $privateFilename = $this->getImgDir() . $file;
        if(!is_file($privateFilename)) {
            return null;
        }

        $basename = 'favicon.ico';
        $publicFilename = $this->getPublicImgDir() . $basename;

        if(!is_file($publicFilename) || filemtime($privateFilename) > filemtime($publicFilename)) {
            // Get the icon and convert it to .ico file if it's not
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $privateFilename);

            if($mimeType !== 'image/vnd.microsoft.icon') {
                $generator = new \PHPICO($privateFilename, array(
                    array(16, 16),
                    array(32, 32),
                    array(48, 48),
                    array(64, 64),
                ));
                $generator->save_ico($publicFilename);
            }
        }

        return $this->getImgUrl($basename);
    }


    /**
     * Check if the plugin is installed. The plugin is installed if it appears in the database
     *
     * @return boolean True if the plugin is installed, False else
     */
    public function isInstalled() {
        return (bool) PluginModel::countElementsByExample(new DBExample(array(
            'name' => $this->name
        )));
    }


    /**
     * Get a plugin short namespace (without \Hawk\Plugins) by it name
     *
     * @param string $name The plugin name
     */
    public static function getShortNamespaceByName($name){
        return preg_replace_callback(
            '/(^|\W|_)(\w?)/', function ($m) {
                return strtoupper($m[2]);
            }, $name
        );
    }

    /**
     * Get a plugin namespace by it name
     *
     * @param string $name The plugin name
     */
    public static function getNamespaceByName($name){
        $namespace = self::getShortNamespaceByName($name);

        return 'Hawk\\Plugins\\' . $namespace;
    }


    /**
     * Get the namespace used for all files in the plugin. The namespace is generated from the plugin name
     *
     * @return string The plugin namespace
     */
    public function getNamespace(){
        return self::getNamespaceByName($this->name);
    }


    /**
     * Get the short namespace (without \Hawk\Plugins) used for all files in the plugin. The namespace is generated from the plugin name
     *
     * @return string The plugin namespace
     */
    public function getShortNamespace(){
        return self::getShortNamespaceByName($this->name);
    }

    /**
     * Instance the plugin installer
     *
     * @return PluginInstaller The instance of the plugin installer
     */
    public function getInstallerInstance(){
        if(isset($this->manager)) {
            return $this->manager;
        }

        $class = '\\' . $this->getNamespace() . '\\Installer';
        if(!empty($class)) {
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
        $model = new PluginModel(array(
            'name' => $this->name,
            'active' => 0
        ));

        $model->save();

        if(!is_dir($this->getUserfilesDir())) {
            mkdir($this->getUserfilesDir(), 0755, true);
        }
        if(!is_dir($this->getPublicStaticDir())) {
            mkdir($this->getPublicStaticDir(), 0755, true);
        }
        if(!is_dir($this->getPublicUserfilesDir())) {
            mkdir($this->getPublicUserfilesDir(), 0755, true);
        }
        if(!is_dir($this->getUserfilesDir())) {
            mkdir($this->getUserfilesDir(), 0755, true);
        }

        try{
            $this->getInstallerInstance()->install();
            App::logger()->notice('The plugin ' . $this->name . ' has been installed');
        }
        catch(\Exception $e){
            PluginModel::deleteByExample(new DBExample(array(
                'name' => $this->name
            )));

            App::logger()->error('En error occured while installing plugin ' . $this->name . ' : ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Uninstall the plugin
     */
    public function uninstall(){
        PluginModel::deleteByExample(new DBExample(array(
            'name' => $this->name
        )));

        if(is_dir($this->getUserfilesDir())) {
            App::fs()->remove($this->getUserfilesDir());
        }

        if(is_dir($this->getPublicStaticDir())) {
            App::fs()->remove($this->getPublicStaticDir());
        }

        if(is_dir($this->getPublicUserfilesDir())) {
            App::fs()->remove($this->getPublicUserfilesDir());
        }

        if(is_dir($this->getUserfilesDir())) {
            App::fs()->remove($this->getUserfilesDir());
        }

        try{
            $this->getInstallerInstance()->uninstall();
            App::logger()->notice('The plugin ' . $this->name . ' has been uninstalled');
        }
        catch(\Exception $e){
            $model = new PluginModel(array(
                'name' => $this->name,
                'active' => 0
            ));

            $model->save();

            App::logger()->error('En error occured while uninstalling plugin ' . $this->name . ' : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if the plugin is active
     *
     * @return boolean True if the plugin is active, False else
     */
    public function isActive(){
        if(!isset($this->active)) {
            $this->active = (bool) PluginModel::countElementsByExample(new DBExample(array(
                'name' => $this->name,
                'active' => 1
            )));
        }

        return $this->active;
    }


    /**
     * Activate the plugin in the database
     */
    public function activate(){
        // Activate the plugin
        $this->active = true;

        $model = new PluginModel(array(
            'name' => $this->name,
            'active' => 1
        ));
        $model->update();

        try{
            $this->getInstallerInstance()->activate();
            App::logger()->notice('The plugin ' . $this->name . ' has been activated');
        }
        catch(\Exception $e){
            $this->active = false;

            $model->active = 0;
            $model->update();

            App::logger()->error('En error occured while activating plugin ' . $this->name . ' : ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Deactive the plugin
     */
    public function deactivate(){
        // Deactivate the plugin
        $this->active = false;

        $model = new PluginModel(array(
            'name' => $this->name,
            'active' => 0
        ));
        $model->update();

        try{
            $this->getInstallerInstance()->deactivate();
            App::logger()->notice('The plugin ' . $this->name . ' has been deactivated');
        }
        catch(\Exception $e){
            $this->active = true;

            $model->active = 1;
            $model->update();

            App::logger()->error('En error occured while deactivating plugin ' . $this->name . ' : ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Update the plugin to a given version
     *
     * @param string $version The version to update the plugin
     */
    public function update($version){
        $updater = $this->getInstallerInstance();

        $method = 'v' . str_replace('.', '_', $version);
        if(method_exists($updater, $method)) {
            $updater->$method();
        }
    }

    /**
     * Complete deletion of plugin
     */
    public function delete(){
        if($this->removable) {
            $directory = $this->getRootDir();

            App::fs()->remove($directory);
        }
    }


    /**
     * Get the permissions declared for this plugin
     */
    public function getPermissions() {
        return Permission::getPluginPermissions($this->getName());
    }


    /**
     * Get the menu items declared for this plugin
     */
    public function getMenuItems() {
        return MenuItem::getPluginMenuItems($this->getName());
    }


    /**
     * Check if plugin exist and if it is active
     *
     * @param string $name The plugin name to test
     *
     * @return bool True if the plugin exists and is active, else False
     */
    public static function existAndIsActive($name){
        $class = self::getNamespaceByName($name) . "\Installer";

        if(class_exists($class)) {
            $plugin = Plugin::get($name);

            if(($plugin != null) && ($plugin->isActive())) {
                return true;
            }
            else {
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * Check if a plugin requires that plugin to be present
     *
     * @return boolean
     */
    public function isMandatoryDependency() {
        $allPlugins = self::getAll(false, false);

        foreach($allPlugins as $plugin) {
            if($plugin->getName() !== $this->getName()) {
                $dependencies = $plugin->getDefinition('dependencies');
                if(!empty($dependencies[$this->getName()]['mandatory'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
