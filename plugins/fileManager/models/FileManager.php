<?php

namespace Hawk\Plugins\FileManager;

class FileManager{

    const ROOT_DIR = '/home/devs/manager/plugins/fileManager/files/';


   public static function getAllPreviewRoute(){
      FileManager::getPreviewRoute('/home/devs/manager/static/plugins/fileManager/userfiles/');
   }
  
  public static function getPreviewRoute($dir){
     $cdir = scandir($dir); 
     foreach ($cdir as $key => $value){ 
        if (!in_array($value,array(".",".."))){ 
           if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){ 
              FileManager::getPreviewRoute($dir . DIRECTORY_SEPARATOR . $value); 
           } 
           else{ 
               Router::get('fileManager-preview-' . $dir . $value, '/fileManager/preview' . $dir . $value, array(
                'action' => 'FileManagerController.getPreviewFile', 
                'where' => array('path' => '*'),
            )); 
           } 
        } 
     }
  }


}