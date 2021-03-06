<?php
/**
 * WysiwygInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the wysiwyg fields in forms. Wysiwyg behavior is computed by CKEditor library
 * http://ckeditor.com/
 *
 * @package Form\Input
 */
class WysiwygInput extends TextareaInput{
    const TYPE = "textarea";

    /**
     * Constructor
     *
     * @param array $param The input parameters. This arguments is an associative array where each key is the name of a property of this class
     */
    public function __construct($param){
        parent::__construct($param);

        $this->attributes['e-wysiwyg'] = '1';
    }


    /**
     * Display the input
     *
     * @return string the HTML result of the input displaying
     */
    public function display(){
        return parent::display() .
        '<script>
            require(["app", "emv"], function(app, EMV) {
                const model = new EMV();

                model.$apply(document.getElementById("' . $this->id . '"));
            });
        </script>';
    }

}
