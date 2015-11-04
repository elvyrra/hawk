<?php
/*** Constant paths ***/
define('LIB_DIR', ROOT_DIR . 'lib/'); // The folder containing all the core classes

define('CUSTOM_LIB_DIR', ROOT_DIR . 'custom-lib/'); // The folder containing the developper custom libraries

define('MAIN_PLUGINS_DIR', ROOT_DIR . 'main-plugins/'); // The folder containing the core plugins

define('PLUGINS_DIR', ROOT_DIR . 'plugins/'); // The folder containing the installed plugins

define('THEMES_DIR', ROOT_DIR . 'themes/'); // The folder containing the themes files

define('CACHE_DIR', ROOT_DIR . 'cache/');

define('LOG_DIR', ROOT_DIR . 'logs/');

define('CACHE_LANG_DIR', CACHE_DIR . 'lang/');

define('USERFILES_DIR', ROOT_DIR . 'userfiles/');

define('USERFILES_THEMES_DIR', USERFILES_DIR . 'themes/');

define('USERFILES_PLUGINS_DIR', USERFILES_DIR . 'plugins/');

define('STATIC_DIR', ROOT_DIR . 'static/');

define('STATIC_THEMES_DIR', STATIC_DIR . 'themes/');

define('STATIC_PLUGINS_DIR', STATIC_DIR . 'plugins/');

define('TMP_DIR', ROOT_DIR . 'tmp/');

// The main db name
define('MAINDB', 'main');

// Encoding
define('ENCODING', 'utf-8');

// Allowed image types
define('ALLOWED_IMAGE_UPLOAD_TYPES', 'image/png image/jpeg image/jpg image/bpm image/gif image/tif');

// Hawk website URL
define('HAWK_SITE_URL', 'http://hawk-site.dev.elvyrra.fr');

// Default configuration values
define('DEFAULT_HTML_TITLE', 'Hawk');
define('DEFAULT_SESSION_ENGINE', 'file');
define('DEFAULT_TIMEZONE', date_default_timezone_get());

// The application version
define('HAWK_VERSION', file_get_contents(ROOT_DIR . 'version.txt'));