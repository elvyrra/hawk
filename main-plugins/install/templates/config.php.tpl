<?php
namespace Hawk;

/** DEFINED BY THE INSTALL PROCESS **/
define('CRYPTO_SALT', '{{ $salt }}');
define('CRYPTO_KEY','{{ $key }}');
define('CRYPTO_IV','{{ $iv }}'); 

/** TO BE OVERRIDEN **/
define('CONFIG_MODE', '{{ $configMode }}');

/** MAIN CONFIGURATION, DEFINED BY THE PROCESS **/
Conf::set(array(
    'rooturl' => '{{ $rooturl }}',
    'db' => array(
        'maindb' => array(
            array(
                'host' => '{{ $host }}',
                'username' => '{{ $username }}',
                'password' => '{{ $password }}',
                'dbname' => '{{ $dbname }}',
            )
        ),
        'prefix' => '{{ $prefix }}',
    ),
    'session' => array(
        'engine' => '{{ $sessionEngine }}',
        'lifetime' => 0
    ),
));

if(defined('CONFIG_MODE') && CONFIG_MODE){
    include ROOT_DIR . 'etc/' . CONFIG_MODE . '.php';
}

if(!defined('DEBUG_MODE')){
    define('DEBUG_MODE', false);
}

if(!defined('DEV_MODE')){
    define('DEV_MODE', false);
}

if(!defined('APP_VERSION')){
    define('APP_VERSION', '{{ $version }}');
}