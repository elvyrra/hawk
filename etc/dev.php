<?php

/** DEVELOPER ZONE **/
define('DEBUG_MODE', true); // Display or not the errors
define('DEV_MODE', true); // Display or not the dev debug bar
define('NO_CACHE', false);

/** Get the app version.
 * it will be used to call static JS and CSS. You can put the last commit
 * id of you use git for example **/
define('APP_VERSION', '1.0');

ini_set('display_errors', true);
error_reporting(E_ALL & ~E_STRICT);