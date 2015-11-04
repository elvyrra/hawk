<?php
namespace Hawk;

/** DEVELOPER ZONE **/
define('DEBUG_MODE', true); // Display or not the errors
define('DEV_MODE', true); // Display or not the dev debug bar

ini_set('display_errors', true);
error_reporting(E_ALL & ~E_STRICT);