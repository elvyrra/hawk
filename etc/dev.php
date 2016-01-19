<?php
namespace Hawk;

// Display or not the errors
define('DEBUG_MODE', true);

// If set to true, then the javascript files won't be uglified
define('DEV_MODE', false);

// Defines if logs are active or not (lower when active)
define('ENABLE_LOG', false);

ini_set('display_errors', true);
error_reporting(E_ALL & ~E_STRICT);