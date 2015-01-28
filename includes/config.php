<?php
/** DEFINED BY THE INSTALL PROCESS **/
define("CRYPTO_SALT", "{crypto_salt}");
define('CRYPTO_KEY','{keykeykeykeykeykeykeykeykeykey}');
define('CRYPTO_IV','{iviviviviviviv}'); 

/** TO BE OVERRIDEN **/
define('CONFIG_MODE', 'dev');

/** MAIN CONFIGURATION, DEFINED BY THE PROCESS **/
Conf::set(array(
	'rooturl' => 'http://mint.elvyrra.fr',
	'db' => array(
		array(
			'host' => 'localhost',
			'username' => 'root',
			'password' => 'eltouristo06',
			'dbname' => 'Mint',
		),	
	),
	'session' => array(
		'system' => 'database'
	),
));

if(defined('CONFIG_MODE') && CONFIG_MODE){
	include ROOT_DIR . 'config/' . CONFIG_MODE . '.php';
}

if(!defined('DEBUG_MODE'))
	define('DEBUG_MODE', false);

if(!defined('DEV_MODE'))
	define('DEV_MODE', false);

if(!defined('APP_VERSION'))
	define('APP_VERSION', '0.1');