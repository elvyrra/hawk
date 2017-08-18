<?php
/**
 * This file contains the main constants declaration for Hawk
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

/**
 * The folder containing the config files for each environment
 */
define('ETC_DIR', ROOT_DIR . 'etc/');

/**
 * The folder containing all the core classes
 */
define('LIB_DIR', ROOT_DIR . 'lib/');

/**
 * The folder containing the developper custom libraries
 */
define('CUSTOM_LIB_DIR', ROOT_DIR . 'custom-lib/');

/**
 * The folder containing the installed plugins
 */
define('PLUGINS_DIR', ROOT_DIR . 'plugins/');

/**
 * The folder containing the themes files
 */
define('THEMES_DIR', ROOT_DIR . 'themes/');

/**
 * The folder contaning cache files
 */
define('CACHE_DIR', ROOT_DIR . 'cache/');

/**
 * The folder contaning language cache files
 */
define('CACHE_LANG_DIR', CACHE_DIR . 'lang/');

/**
 * The folder contaning log files
 */
define('LOG_DIR', ROOT_DIR . 'logs/');

/**
 * The folder containing user files
 */
define('USERFILES_DIR', ROOT_DIR . 'userfiles/');

/**
 * The folder containing userfiles for themes
 */
define('USERFILES_THEMES_DIR', USERFILES_DIR . 'themes/');

/**
 * The folder containing userfiles for plugins
 */
define('USERFILES_PLUGINS_DIR', USERFILES_DIR . 'plugins/');

/**
 * The folder containing static files
 */
define('STATIC_DIR', ROOT_DIR . 'static/');

/**
 * The folder containing themes static files
 */
define('STATIC_THEMES_DIR', STATIC_DIR . 'themes/');

/**
 * The folder containing plugins static files
 */
define('STATIC_PLUGINS_DIR', STATIC_DIR . 'plugins/');

/**
 * The folder contaning temporary files
 */
define('TMP_DIR', ROOT_DIR . 'tmp/');

/**
 * The main db name
 */
define('MAINDB', 'main');

/**
 * The default encoding
 */
define('ENCODING', 'utf-8');

/**
 * Allowed image types
 */
define('ALLOWED_IMAGE_UPLOAD_TYPES', 'image/png image/jpeg image/jpg image/bpm image/gif image/tif');

/**
 * Default configuration values
 */
define('DEFAULT_HTML_TITLE', 'Hawk');

/**
 * The default timezone
 */
define('DEFAULT_TIMEZONE', date_default_timezone_get());

/**
 * The application version
 */
$package = json_decode(file_get_contents(ROOT_DIR . 'package.json'));
define('HAWK_VERSION', $package->version);


/**
 * The constants for hashing passwords
 */
define('HASH_ALGO', 5); // SHA256
define('HASH_SALT_LENGTH', 8);