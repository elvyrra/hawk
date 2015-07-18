<?php

if(!Conf::has('db')){
    Router::get('install', '/install', array('action' => 'InstallController.setLanguage'));

    Router::any('install-settings', '/install/settings/{language}', array('where' => array('language' => '[a-z]{2}'), 'action' => 'InstallController.settings'));
}
