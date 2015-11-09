<?php

/**
 * UpdateController.php
 */

namespace Hawk\Plugins\Admin;

/**
 * This controller is used to update Hawk, the plugins and the themes installed on the instance
 */
class UpdateController extends Controller{

    /**
     * Display all available updates
     */
    public function index(){
        $tabs = array();
        $updates = array();
        $api = new HawkApi;
        
        // Get updates on core
        try{
            $coreUpdates = $api->getCoreAvailableUpdates();
        }
        catch(\Hawk\HawkApiException $e){
            $coreUpdates = array();
        }
        
        $updates['core'] = $coreUpdates;

        $page = View::make(Plugin::current()->getView('updates.tpl'), array(
            'updates' => $updates,
        ));

        $this->addJavaScript(Plugin::current()->getJsUrl('updates.js'));
        Lang::addKeysToJavascript('admin.update-page-confirm-update-hawk');

        return NoSidebarTab::make(array(
            'title' => Lang::get('admin.update-page-title'),
            'icon' => 'refresh',
            'page' => $page
        ));
    }


    /**
     * Update Hawk
     */
    public function updateHawk(){
        try{
            $api = new HawkApi;

            $nextVersions = $api->getCoreAvailableUpdates();
            
            if(empty($nextVersions)){
                throw new \Exception("No newer version is available for Hawk");                
            }

            // Download the update archive
            $archive = $api->getCoreUpdateArchive($this->version);            

            // Extract the downloaded file
            $zip = new \ZipArchive;
            if($zip->open($archive) !== true){
                throw new \Exception('Impossible to open the zip archive');
            }
            $zip->extractTo(TMP_DIR);

            // Put all modified or added files in the right folder
            $folder = TMP_DIR . 'update-v' . $this->version . '/';
            FileSystem::copy($folder . 'to-update/*', ROOT_DIR);

            // Delete the files to delete
            $toDeleteFiles = explode(PHP_EOL, file_get_contents($folder . 'to-delete.txt'));

            foreach($toDeleteFiles as $file){
                if(is_file(ROOT_DIR . $file)){
                    unlink(ROOT_DIR . $file);
                }
            }

            // Execute the update methods if exist
            $updater = new HawkUpdater;
            $methods = get_class_methods($updater);

            foreach($nextVersions as $v){
                $method = 'v' . str_replace('.', '_', $v['version']);
                if(method_exists($updater, $method)){
                    $updater->$method();
                }
            }
            

            // Remove temporary files and folders
            FileSystem::remove($folder);
            FileSystem::remove($archive);

            $response = array('status' => true);
        }
        catch(\Exception $e){
            $response = array('status' => false, 'message' => DEBUG_MODE ? $e->getMessage() : Lang::get('admin.update-hawk-error'));
        }

        Response::setJson();
        Response::end($response);
    }


    /**
     * Update a plugin
     */
    public function updatePlugin(){}

    /**
     * Update a theme
     */
    public function updateTheme(){}
}