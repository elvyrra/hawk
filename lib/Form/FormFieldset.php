<?php
/**
 * FormFieldset.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the behavior of a form fieldset
 *
 * @package Form
 */
class FormFieldset{
    use Utils;

    /**
     * The fieldset name
     */
    public $name,

    /**
     * The legend of the fieldset
     */
    $legend,

    /**
     * The inputs in this fieldset
     */
    $inputs,

    /**
     * The form this fieldset is associated to
     */
    $form,

    /**
     * The id of the legend tag
     */
    $legendId = '';

    /**
     * Constructor
     *
     * @param Form   $form   The form this fieldset is asssociated to
     * @param string $name   The fieldset name
     * @param array  $inputs The inputs in this fieldset
     * @param array  $params The parameters to apply to this fieldset
     */
    public function __construct($form, $name, $inputs= array(), $params = array()){
        $this->name = $name;
        $this->form = $form;
        $this->id = $form->id . '-' . $this->name . '-fieldset';
        $this->inputs = $inputs;
        $this->map($params);

        if($this->legend) {
            $this->legendId = $form->id . '-' . $this->name . '-legend';
        }
    }


    /**
     * Set a parameter of the fieldset
     *
     * @param string $param the name of the parameter
     * @param mixed  $value The value to apply
     */
    public function setParam($param, $value){
        $this->$param = $value;
    }

    /**
     * Display the parameter
     *
     * @return string The HTML result to display
     */
    public function __toString(){
        return View::make(
            Theme::getSelected()->getView(Form::VIEWS_DIR . 'form-fieldset.tpl'), array(
            'fieldset' => $this,
            )
        );
    }
}
