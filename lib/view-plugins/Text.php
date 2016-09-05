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
        $encoded = isset($data['encoded']);

        unset($data['key']);
        unset($data['encoded']);

        $text = \Hawk\Lang::get($this->key, $data, empty($this->number) ? 0 : $this->number);

        if($encoded) {
            return htmlentities($text, ENT_QUOTES);
        }

        return $text;
    }
}
