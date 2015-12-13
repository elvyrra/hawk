<?php

namespace Hawk\Plugins\Install;


if(!App::conf()->has('db')){       
    App::router()->setProperties(
        array('namespace' => __NAMESPACE__), 
        function(){
            App::router()->get('install', '/install', array('action' => 'InstallController.setLanguage'));

            App::router()->any('install-settings', '/install/settings/{language}', array('where' => array('language' => '[a-z]{2}'), 'action' => 'InstallController.settings'));
        }
    );
}