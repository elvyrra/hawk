<?php

/**
 * UpdateController.class.php
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

        $api = new HawkApi;
        
        // Get updates on core
        $coreUpdates = $api->getCoreUpdates();
        if(count($coreUpdates)){
            $tabs[] = array(
                'title' => Lang::get('admin.update-page-tab-hawk-title'),
                'content' => $this->indexHawk($coreUpdates)
            );
        }

        $page = View::make(Plugin::current()->getView('updates.tpl'), array(
            'tabs' => $tabs
        ));

        $this->addJavaScript(Plugin::current()->getJsUrl('updates.js'));
        return NoSidebarTab::make(array(
            'title' => Lang::get('admin.update-page-title'),
            'icon' => 'refresh',
            'page' => $page
        ));
    }


    /**
     * Display the page to update Hawk
     */
    private function indexHawk($updates){

        $button = new ButtonInput(array(            
            'class' => 'btn-warning update-hawk',            
            'icon' => 'refresh',
            'value' => Lang::get('admin.update-page-update-hawk-btn', array('version' => end($updates)['version'])),
            'attributes' => array(
                'data-to' => end($updates)['version']
            )
        ));

        Lang::addKeysToJavaScript('admin.update-page-confirm-update-hawk');

        return Lang::get('admin.update-page-current-hawk-version', array('version' => file_get_contents(ROOT_DIR . 'version.txt'))) . $button->display();
    }



    /**
     * Update Hawk
     */
    public function updateHawk(){
        try{
            $api = new HawkApi;

            $nextVersions = $api->getCoreUpdates();

            // Download the update archive
            $archive = $api->getCoreUpdateArchive($this->version, $errors);
            if(! $archive){
                // The download failed
                throw new \Exception($errors['message']);
            }

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