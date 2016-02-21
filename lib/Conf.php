<?php
/**
 * Conf.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to get and set the application base configuration
 *
 * @package Core
 */
final class Conf extends Singleton{
    /**
     * The application configuration cache
     *
     * @var array
     */
    private $conf;


    /**
     * The configuration instance
     *
     * @var Conf
     */
    protected static $instance;


    /**
     * Get a configuration value
     *
     * @param string $option The name of the configuration parameter to get
     *
     * @return mixed The configuration value
     */
    public function get($option = ''){
        if(!isset($this->conf)) {
            return null;
        }

        if(empty($option)) {
            return $this->conf;
        }
        else{
            $fields = explode('.', $option);
            $tmp = $this->conf;
            foreach($fields as $field){
                if(isset($tmp[$field])) {
                    $tmp = $tmp[$field];
                }
                else{
                    return null;
                }
            }
            return $tmp;
        }
    }


    /**
     * Set a configuration parameter
     *
     * @param string $option The name of the parameter to set
     * @param mixed  $value  The value to set
     */
    public function set($option, $value = null){
        if($value == null) {
            $this->conf = $option;
        }
        else{
            $fields = explode('.', $option);
            $tmp = &$this->conf;
            foreach($fields as $field){
                $tmp = &$tmp[$field];
            }
            $tmp = $value;
        }
    }


    /**
     * Check if a configuration parameter exists
     *
     * @param string $option The parameter name to find
     *
     * @return boolean True if the parameter exists in the application configuration, false else
     */
    public function has($option){
        $value = $this->get($option);
        return $value !== null;
    }

}