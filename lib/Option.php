<?php
/**
 * Option.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to access and set application options
 *
 * @package Core
 */
class Option{
    /**
     * The options loaded from database and saved in memory
     */
    private static $options = array();

    /**
     * The file containing the cache of options
     */

    /**
     * Get the value of an option
     *
     * @param string $name the option to get the value of, formatted like : <plugin>.<key>
     *
     * @return string the value og the option
     */
    public static function get($name) {
        list($plugin, $key) = explode('.', $name);

        if(!isset(self::$options[$plugin][$key])) {
            self::getPluginOptions($plugin);
        }

        return isset(self::$options[$plugin][$key]) ? self::$options[$plugin][$key] : null;
    }

    /**
     * Load all the options of a given plugin
     *
     * @param string $plugin The plugin to find the options of
     *
     * @return array All the options of the plugin with their values
     */
    public static function getPluginOptions($plugin){
        if(!App::isInstalled()) {
            return array();
        }

        if(!isset(self::$options[$plugin])) {
            $options = OptionModel::getListByExample(new DBExample(array(
                'plugin' => $plugin
            )));

            self::$options[$plugin] = array();
            foreach($options as $option){
                self::$options[$plugin][$option->key] = $option->value;
            }
        }
        return self::$options[$plugin];
    }


    /**
     * Add an option or update an existing option value
     *
     * @param string $name  The name of the option, formatted like : <plugin>.<key>
     * @param mixed  $value The value to set for this option
     */
    public static function set($name, $value){
        list($plugin, $key) = explode('.', $name);
        self::$options[$plugin][$key] = $value;

        OptionModel::getDbInstance()->replace(
            OptionModel::getTable(),
            array(
                'plugin' => $plugin,
                'key' => $key,
                'value' => $value
            )
        );
    }


    /**
     * Remove an option
     *
     * @param string $name The name of the option, formatted like : <plugin>.<key>
     */
    public static function delete($name){
        list($plugin, $key) = explode('.', $name);

        OptionModel::deleteByExample(new DBExample(array(
            'plugin' => $plugin,
            'key' => $key
        )));
    }

    /**
     * Get All options
     *
     * @return array The array containing all options
     */
    public static function getAll() {
        return self::$options;
    }
}

