<?php
/**
 * Initialise the plugin install
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\Plugins\Install;


if(!App::conf()->has('db')) {

    // First install page : Choose the language
    App::router()->get('install', '/install', array('action' => 'InstallController.setLanguage'));

    // Install Hawk
    App::router()->any('install-settings', '/install/settings/{language}', array(
        'where' => array(
            'language' => '[a-z]{2}'
        ),
        'action' => 'InstallController.settings'
    ));
}

