<?php
/**
 * FormInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the general behavior of inputs in forms. This class is associated to Form class
 *
 * @package Form\Input
 */
class FormInput{
    use Utils;

    /**
     * Attributes properties that can be called at input initialization, and there type
     *
     * @static array $attr
     */
    public static $attr = array(
        'checked' => 'bool',
        'class' => 'text',
        'cols' => 'int',
        'disabled' => 'bool',
        'id' => 'text',
        'maxlength' => 'int',
        'multiple' => 'bool',
        'name' => 'text',
        'placeholder' => 'html',
        'readonly' => 'bool',
        'rows' => 'int',
        'size' => 'int',
        'style' => 'text',
        'title' => 'html',
        'type' => 'text',
        'value' => 'html',
        'autocomplete' => 'text'
    );

    /**
     * HTML input attributes, used to apply non listed attributes, as aria or data attributes
     *
     * @var array
     */
    public $attributes = array();

    /**
     * HTML class attribute
     *
     * @var string
     */
    public $class = '';

    /**
     * HTML title attribute
     *
     * @var string
     */
    public $title = '';

    /**
     * HTML style attribute
     *
     * @var string
     */
    public $style = '';

    /**
     * HTMl name attribute (required)
     *
     * @var string
     */
    public $name;

    /**
     * HTML id attribute. If not given, it will be generated form a uniqid and the name of the input
     *
     * @var string
     */
    public $id;

    /**
     * The default input value
     *
     * @var string
     */
    public $default;

    /**
     * Field value
     *
     * @var string
     */
    public $value;

    /**
     * HTML placeholder attribute
     *
     * @var string
     */
    public $placeholder = '';

    /**
     * HTML maxlength attribute
     *
     * @var int
     */
    public $maxlength = 0;

    /**
     * HTML disabled attribute
     *
     * @var boolean
     */
    public $disabled = false;

    /**
     * HTML readonly attribute
     *
     * @var boolean
     */
    public $readonly = false;

    /**
     * Defines if the field is required or not
     *
     * @var boolean
     */
    public $required = false;

    /**
     * Define this property to display the errors on another input, giving the input name
     * Can be used for example for hidden fields that value is dynamically filled client-side
     *
     * @var string
     */
    public $errorAt = '';

    /**
     * Defines if the field has to be displayed on a new line
     *
     * @var boolean
     */
    public $nl = true;

    /**
     * If set to true, the input won't be displayed. Can be usefull to hide a field in a specific usecase
     *
     * @var boolean
     */
    public $notDisplayed = false;

    /**
     * If set to true, the input will be displayed but with a display none
     *
     * @var boolean
     */
    public $hidden = false;

    /**
     * String to display before the input
     *
     * @var string
     */
    public $before = '';

    /**
     * String to display after the input
     *
     * @var string
     */
    public $after = '';

    /**
     * The label to display with the input
     *
     * @var string
     */
    public $label = '';

    /**
     * If set to true, the input will be displayed before the label (defaultly, it is displayed after)
     *
     * @var boolean
     */
    public $beforeLabel = false;

    /**
     * The style attribute to apply to the label
     *
     * @var string
     */
    public $labelStyle = '';

    /**
     * The regular expression the input value must valid
     *
     * @var string
     */
    public $pattern = '';

    /**
     * The input validators. Other parameters defaultly fills this property, but can be completed at the instanciation
     *
     * @var array
     */
    public $validators = array();

    /**
     * Mask to apply on the HTML input
     *
     * @var string
     */
    public $mask = '';

    /**
     * Defines if the value of this field has to be unique in the database
     *
     * @var boolean
     */
    public $unique = false;

    /**
     * The type of data in the database
     *
     * @var string
     */
    public $dataType = '';

    /**
     * The database field attached to the input
     *
     * @var string
     */
    public $field;


    /**
     * Define the value defined as "empty", default ""
     *
     * @var mixed
     */
    public $emptyValue = '';

    /**
     * Define the class on the label
     *
     * @var string
     */
    public $labelClass = '';

    /**
     * If set to true, then this field won't be searched and updating in the database
     *
     * @var boolean
     */
    public $independant = false;

    /**
     * Define if the input data has to be inserted in database when the form is submitted
     *
     * @var boolean
     */
    public $insert = true;

    /**
     * The input view filename
     *
     * @var string
     */
    public $tpl = '';

    /**
     * This constant is used to force the property $independant for all instances of a input class that extends this class
     */
    const INDEPENDANT = false;

    /**
     * Constructor
     *
     * @param array $param The input parameters. This arguments is an associative array where each key is the name of a property of this class
     */
    public function __construct($param) {

        $this->setParam($param);

        if(!isset($this->name)) {
            $this->name = $this->field;
        }

        if(!isset($this->id)) {
            $this->id = uniqid().'-'. str_replace(array('.', '#', '[', ']','>'), '-', $this->name);
        }

        $this->type = static::TYPE;

        if(empty($this->tpl)) {
            $theme = Theme::getSelected();

            $file = Theme::getSelected()->getView(Form::VIEWS_DIR . 'form-input-' . static::TYPE . '.tpl');
            $this->tpl = is_file($file) ? $file : Theme::getSelected()->getView(Form::VIEWS_DIR . 'form-input.tpl');
        }
    }


    /**
     * Set the input parameters
     *
     * @param string|array $param If set as a string, the name of the parameter to set. If an array, a set of parameters
     * @param mixed        $value The value to set, in case the parameter $param is a string
     */
    public function setParam($param, $value = null){
        if(is_array($param)) {
            $this->map($param);
        }
        else{
            $this->$param = $value;
        }
    }


    /**
     * Create a input instance with it parameters
     *
     * @param array $param The input parameters.
     *                     This arguments is an associative array where each key is the name of a property of this class
     *
     * @return FormInput The created instance
     */
    public static function create($param){
        $class = get_called_class();

        return new $class($param);
    }


    /**
     * Display the input
     *
     * @return string the HTML result of the input displaying
     */
    public function __toString(){
        return $this->display();
    }

    /**
     * Display the input (alias method)
     *
     * @return string the HTML result of the input displaying
     */
    public function display(){
        try{
            $theme = Theme::getSelected();

            if($this->name == $this->errorAt) {
                unset($this->errorAt);
            }

            $inputLabel = $this->label ?
                View::make($theme->getView(Form::VIEWS_DIR . 'form-input-label.tpl'), array(
                    'input' => $this
                )) :
                '';

            $inputDisplay = View::make($this->tpl, array(
                'input' => $this
            ));

            return View::make($theme->getView(Form::VIEWS_DIR . 'form-input-block.tpl'), array(
                'input' => $this,
                'inputLabel' => $inputLabel,
                'inputDisplay' => $inputDisplay
            ));
        }
        catch(\Exception $e){
            App::errorHandler()->exception($e);
        }
    }

    /**
     * Check the submitted value of the input
     *
     * @param Form $form The form the input is associated with
     *
     * @return bool True if the submitted value is valid, else false
     */
    public function check(&$form = null){
        if(empty($this->errorAt)) {
            $this->errorAt = $this->name;
        }

        // Check, if the field is required, that a value was submitted
        if(!empty($this->required) && ((string)$this->value == '' || $this->emptyValue && $this->value === $this->emptyValue)) {
            // The field is required but not filled
            $form && $form->error($this->errorAt, Lang::get('form.required-field'));
            return false;
        }

        // Check the format of the field is correct
        if(!empty($this->value) && !empty($this->pattern)) {
            // test the format of the field
            if(!preg_match($this->pattern, $this->value)) {
                // the format of the field value is not correct
                if ($form) {
                    $message = '';
                    if(isset($this->errorMessage)) {
                        $message = $this->errorMessage;
                    }
                    elseif(Lang::exists('form.'.static::TYPE."-format")) {
                        $message = Lang::get('form.'.static::TYPE."-format");
                    }
                    else {
                        $message = Lang::get('form.field-format');
                    }

                    $form->error($this->errorAt, $message);
                }
                return false;
            }
        }

        // If the value of this field must be unique in the database, check this unicity is not compromised
        if(!empty($this->value) && $this->unique && $form) {
            $example = array(
                array($this->name => $this->dbvalue())
            );

            if($form->object) {
                $example['$not'] = $form->reference;
            }

            $example = new DBExample($example);

            $model = $form->model;
            if($model::getByExample($example)) {
                // The field must be unique but is not
                $form->error($this->errorAt, Lang::get('form.unique-field'));
                return false;
            }
        }

        // Check custom validators
        if(!empty($this->validators)) {
            foreach($this->validators as $validator){
                $error = '';
                if(is_callable($validator) && !$validator($this, $error)) {
                    $form->error($this->errorAt, $error);
                    return false;
                }
            }
        }

        // The field is correctly filled (for the common checks)
        return true;
    }

    /**
     * Return the value, formatted for the SQL database
     *
     * @return mixed The value, formatted for the database
     */
    public function dbvalue(){
        switch(strtolower($this->dataType)){
            case "boolean" :
            case "bool" :
                return (bool) $this->value;
            break;

            case "integer" :
                return (int) $this->value;
            break;

            case "numeric" :
            case "float" :
                return (float) $this->value;
            break;

            default :
                return $this->value;
            break;
        }
    }


    /**
     * Set the value to the input
     *
     * @param mixed $value The value to set
     */
    public function setValue($value){
        $this->value = $value;
    }


    /**
     * Create an input from an array, automaticall finding it type
     *
     * @param array $parameters The input parameters, formatted as the constructor parameters, and including a 'type' data
     *
     * @return FormInput The input instance
     */
    public static function getInstance($parameters) {
        $classname = get_called_class();

        if($classname === __CLASS__) {
            // Detect the class name to instance
            if(!isset($parameters['type'])) {
                $parameters['type'] = 'text';
            }
            $classname = ucwords($parameters['type']).'Input';

            // Remove the 'type' data, only used to find out the classname
            unset($parameters['type']);
        }

        // Create the instance
        return new $classname($parameters);
    }
}
