<?php
/**
 * TextareaInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the behavior of textareas
 *
 * @package Form\Input
 */
class TextareaInput extends FormInput{
    const TYPE = 'textarea';

    /**
     * The attribute 'rows'
     *
     * @var int
     */
    public $rows = 5,

    /**
     * The attribute 'cols'
     *
     * @var int
     */
    $cols = 30;
}
