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
     *
     * @var string
     */
    public $name,

    /**
     * The legend of the fieldset
     *
     * @var string
     */
    $legend,

    /**
     * The inputs in this fieldset
     *
     * @var array
     */
    $inputs = array(),

    /**
     * The form this fieldset is associated to
     *
     * @var Form
     */
    $form,

    /**
     * The id of the legend tag
     *
     * @var string
     */
    $legendId = '',

    /**
     * Defines if the fieldset must not be displayed
     *
     * @var bool
     */
    $notDisplayed = false,

    /**
     * HTML attibutes to set on the fieldset tag
     *
     * @var array
     */
    $attributes = array();

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
        $this->map($params);

        if($this->legend) {
            $this->legendId = $form->id . '-' . $this->name . '-legend';
        }

        foreach($inputs as &$input){
            $form->addInput($input, $this->name);
            $this->inputs[$input->name] = $input;
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
    public function __toString() {
        if($this->notDisplayed) {
            return '';
        }

        return View::make(Theme::getSelected()->getView(Form::VIEWS_DIR . 'form-fieldset.tpl'), array(
            'fieldset' => $this,
        ));
    }
}
