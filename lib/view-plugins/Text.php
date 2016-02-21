<?php
/**
 * Text.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\View\Plugins;

/**
 * This class is used in view to display a language key
 *
 * @package View\Plugins
 */
class Text extends \Hawk\ViewPlugin{
    /**
     * The language key
     *
     * @var string
     */
    public $key,

    /**
     * The key index, used if the key is an array of keys, defined for singular and plural
     */
    $number;

    /**
     * Display the language key
     */
    public function display(){
        $data = $this->params;
        unset($data['key']);

        return \Hawk\Lang::get($this->key, $data, empty($this->number) ? 0 : $this->number);
    }
}
