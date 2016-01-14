<?php

namespace Hawk\Plugins\FileManager;

App::router()->setProperties(
    array(
        'namespace' => __NAMESPACE__,
        'prefix' => '/fileManager'
    ),
    function(){
        App::router()->auth(App::session()->isConnected(), function(){
            // Index
            App::router()->get('fileManager-index', '/index', array('action' => 'FileManagerController.index'));

            App::router()->any('fileManager-editRootFolder', '/editRootFolder', array('action' => 'FileManagerController.editRootFolder'));
          
            App::router()->any('fileManager-editFolder', '/editFolder', array('action' => 'FileManagerController.editFolder'));
          
            App::router()->any('fileManager-editFile', '/editFile', array('action' => 'FileManagerController.editFile'));
            
            App::router()->get('fileManager-preview', '/preview', array('action' => 'FileManagerController.getPreviewFile'));
        });
    }
);