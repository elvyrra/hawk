<?php
/**
 * Form.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to generate, display and treat forms.
 *
 * @package Form
 */
class Form{
    use Utils;

    const NO_EXIT = false;
    const EXIT_JSON = true;

    const VIEWS_DIR = 'form/';

    // Submission status
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_CHECK_ERROR = 'check-error';

    // Submission return codes
    const HTTP_CODE_SUCCESS = 200; // OK
    const HTTP_CODE_CHECK_ERROR = 412; // Data format error
    const HTTP_CODE_ERROR = 424; // Treatment error

    // Actions
    const ACTION_REGISTER = 'register';
    const ACTION_DELETE = 'delete';

    // Default model for form
    const DEFAULT_MODEL = "GenericModel";


    /**
     * The submit method (Default : POST)
     */
    public $method = 'post',

    /**
     * The form name
     *
     * @var string
     */
    $name = '',

    /**
     * The form id
     *
     * @var string
     */
    $id = '',

    /**
     * The model class used for display and database treatment
     *
     * @var string
     */
    $model = self::DEFAULT_MODEL,

    /**
     * The object treated by the form
     *
     * @var Object
     */
    $object,

    /**
     * The width of the input labels (if defined, overrides the default label width defined in the theme)
     *
     * @var string
     */
    $labelWidth = '',

    /**
     * The submission status.
     * This variable can be used if you want to know the treatment status before executing other instructions
     *
     * @var string
     */
    $status = null,

    /**
     * The number of columns to display the form. Each fieldset will be displayed on a column.
     * For example, if you define 6 fieldsets in your form, and select 3 for this property,
     * the form will be displayed on 2 lines, with 3 fieldsets by line.
     *
     * @var int
     */
    $columns = 1,


    /**
     * This property can be set if you want to apply a css class to the form
     *
     * @var string
     */
    $class = '',


    /**
     * Defines if the form can autocompleted (Default true)
     *
     * @var bool
     */
    $autocomplete = true,


    /**
     * The form fieldsets
     *
     * @var array
     */
    $fieldsets = array(),

    /**
     * The form inputs
     *
     * @var array
     */
    $inputs = array(),

    /**
     * Defines if the form do an AJAX uplaod
     *
     * @var bool
     */
    $upload = false,

    /**
     * If set to true, no return message will be displayed when the form is submitted
     *
     * @var bool
     */
    $nomessage = false,


    /**
     * Defines the form action. Default value is the current URL
     *
     * @var string
     */
    $action = '',

    /**
     * The data returned by the form
     *
     * @var array
     */
    $returns = array(),

    /**
     * The form errors
     *
     * @var array
     */
    $errors = array(),

    /**
     * The reference to get the object in the database and update it. This property must be displayed as :
     * array(
     *     'field' => 'value',
     *     'field2' => 'value2'
     * )
     *
     * @var array
     */
    $reference = array(),

    /**
     * Additional HTML attributes to set on the form tag
     *
     * @var array
     */
    $attributes = array();

    /**
     * The database example, generated from the reference, to find the object to display and treat in the database
     *
     * @var DBExample
     */
    private $example = null,

    /**
     * The action that is performed on form submission
     *
     * @var sting
     */
    $dbaction = self::ACTION_REGISTER;

    /**
     * Form instances
     *
     * @var array
     */
    private static $instances = array();


    /**
     * Constructor
     *
     * @param array $param The form parameters
     */
    public function __construct($param = array()){
        /*
        * Default values
        */
        $this->action = App::request()->getUri();

        // Get the parameters of the instance
        $data = $param;
        unset($data['fieldsets']);
        $this->setParam($data);

        if(!$this->name) {
            $this->name = $this->id;
        }

        if(!in_array($this->columns, array(1,2,3,4,6,12))) {
            $this->columns = 1;
        }

        if(!class_exists($this->model)) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $reflection = new \ReflectionClass($trace[1]['class']);
            $this->model = $reflection->getNamespaceName() . '\\' . $this->model;
        }

        if(!isset($data['object'])) {
            if(isset($this->model) && !empty($this->reference)) {
                $model = $this->model;
                $this->example = new DBExample($this->reference);
                $this->object = $model::getByExample($this->example);
            }
            else{
                $this->object = null;
            }
        }
        else{
            $this->model = get_class($this->object);
            $model = $this->model;
            $id = $model::getPrimaryColumn();
            $this->reference = array($id => $this->object->$id);
        }

        $this->new = $this->object === null;


        // Get the fields in the "fields" instance property, and add the default values for the fieldsets and fields
        $this->fieldsets = array();
        if(!empty($param['fieldsets'])) {
            foreach($param['fieldsets'] as $name => &$fieldset){
                $inputs = array();
                $params = array();

                $this->addFieldset(new FormFieldset($this, $name));

                foreach($fieldset as $key => &$field){
                    if($field instanceof FormInput) {
                        $this->addInput($field, $name);
                        if($field instanceof FileInput) {
                            $this->upload = true;
                        }
                    }
                    else {
                        $this->fieldsets[$name]->setParam($key, $field);
                    }
                }
            }
        }
        else{
            $this->addFieldset(new FormFieldset($this, 'form'));
            foreach($this->inputs as &$field){
                if($field instanceof FormInput) {
                    $this->addInput($field, 'form');

                    if($field instanceof FileInput) {
                        $this->upload = true;
                    }
                }
            }
        }

        // get the data of the form to display or register
        $this->reload();

        self::$instances[$this->id] = $this;


        $event = new Event(
            'form.' . $this->id . '.instanciated', array(
            'form' => $this
            )
        );
        $event->trigger();
    }


    /**
     * Get a form instance
     *
     * @param string $id the form id to get
     *
     * @static
     *
     * @return Form The form instance
     */
    public static function getInstance($id){
        if(isset(self::$instances[$id])) {
            return self::$instances[$id];
        }
        else{
            return null;
        }
    }

    /**
     * Set form parameters
     *
     * @param mixed $param The parameter name to set.
     *                     If this parameter is an array, then this function will set all parameters defined
     *                     by the keys of this array with the associated value
     * @param mixed $value The value to apply to the parameter
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
     * Reload the form data
     */
    public function reload(){
        // Set default value
        $data = array();
        foreach($this->inputs as $name => $field){
            if(isset($field->default)) {
                $data[$name] = $field->default;
            }

            if(isset($field->value)) {
                $data[$name] = $field->value;
            }
            elseif(!$this->submitted() && isset($this->object->$name)) {
                $data[$name] = $this->object->$name;
            }
        }

        if($this->submitted()) {
            $data = strtolower($this->method) == 'get' ? App::request()->getParams() : App::request()->getBody();
        }

        // Set the value in all inputs instances
        $this->setData($data);
    }


    /**
     * Set the values for field
     *
     * @param array  $data   The data to set, where the keys are the names of the field,
     *                       and the array values, the values to affect
     * @param string $prefix A prefix to apply on the field name,
     *                       if it is defined as an array (used internally to the class)
     */
    public function setData($data, $prefix = '') {
        foreach($data as $key => $value) {
            $field = $prefix ? $prefix."[$key]" : $key;
            if(isset($this->inputs[$field])) {
                $this->inputs[$field]->setValue($value);
            }
            elseif(is_array($value)) {
                $this->setData($value, $field);
            }

        }
    }


    /**
     * Get data of the form
     *
     * @param string $name If set, the function will return the value of the field,
     *                     else it will return an array containing all field values
     *
     * @return mixed If $name is set, the function will return the value of the field,
     *               else it will return an array containing all field values
     */
    public function getData($name = null){
        if($name) {
            return $this->inputs[$name]->value;
        }
        else{
            $result = array();
            foreach($this->inputs as $name => $field){
                $result[$name] = $field->value;
            }

            return $result;
        }
    }


    /**
     * Add a fieldset to the form
     *
     * @param FormFieldset $fieldset The fieldset to add to the form
     */
    public function addFieldset(FormFieldset $fieldset){
        $this->fieldsets[$fieldset->name] = $fieldset;
    }

    /**
     * Add an input to the form
     *
     * @param FormInput $input    The input to insert in the form
     * @param string    $fieldset (optionnal) The fieldset where to insert the input.
     *                            If not set, the input will be just included in $form->inputs, out of any fieldset
     */
    public function addInput(FormInput $input, $fieldset = ''){
        if($input::INDEPENDANT) {
            // This field is independant from the database
            $input->independant = true;
        }

        $labelWidth = $this->labelWidth;
        if(isset($this->fieldsets[$fieldset]->labelWidth)) {
            $labelWidth = $this->fieldsets[$fieldset]->labelWidth;
        }
        if(isset($input->labelWidth)) {
            $labelWidth = $input->labelWidth;
        }
        $input->labelWidth = $labelWidth;

        $this->inputs[$input->name] = &$input;

        if($fieldset) {
            $this->fieldsets[$fieldset]->inputs[$input->name] = &$input;
        }
    }


    /**
     * Defines if the form has been submitted, and if so, return the action to perform (submitted or delete)
     *
     * @return mixed If the form is not submitted, this function will return FALSE.
     *               Else, the function will return 'register' or 'delete', depending on the user action
     */
    public function submitted(){
        if(App::request()->getMethod() == "delete") {
            return self::ACTION_DELETE;
        }

        $action = $this->method == 'get' ? App::request()->getParams('_submittedForm') : App::request()->getBody('_submittedForm');
        return $action ? $action : false;
    }


    /**
     * This method is used when you define your own template for displaying the form content.
     * It will wrap the form content with the <form> tag, and all the parameters defined for this form
     *
     * @param string $content The form content to wrap
     *
     * @return string The HTML result
     */
    public function wrap($content){
        App::logger()->info('display form ' . $this->id);

        // Filter input data that can be sent to the client
        $clientVars = array(
            'id',
            'type',
            'name',
            'required',
            'emptyValue',
            'pattern',
            'minimum',
            'maximum',
            'compare',
            'errorAt',
            'label',
            'invitation'
        );
        $clientInputs = array();
        foreach($this->inputs as $field){
            $clientInputs[$field->name] = array_filter(
                get_object_vars($field),
                function ($key) use ($clientVars) {
                    return in_array($key, $clientVars);
                },
                ARRAY_FILTER_USE_KEY
            );

            $clientInputs[$field->name]['type'] = $field::TYPE;
        }

        // Generate the script to include the form in the application, client side
        $inputs = json_encode($clientInputs, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK);
        $errors = json_encode($this->errors, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK);

        return
        View::make(Theme::getSelected()->getView(Form::VIEWS_DIR . 'form.tpl'), array(
            'form' => $this,
            'content' => $content,
        )) .

        View::make(Plugin::get('main')->getView('form.js.tpl'), array(
            'form' => $this,
            'inputs' => $inputs,
            'errors' => $errors
        ));
    }

    /**
     * Display the form (alias of display method)
     *
     * @return string The HTML result of form displaying
     */
    public function __toString(){
        return $this->display();
    }

    /**
     * Display the form
     *
     * @return string The HTML result of form displaying
     */
    public function display(){
        try{
            if(empty($this->fieldsets)) {
                // Generate a fake fieldset, to keep the same engine for forms that have fieldsets or not
                $this->addFieldset(new FormFieldset($this, ''));
                foreach ($this->inputs as $name => $input) {
                    $this->fieldsets['']->inputs[$name] = &$input;
                }
            }

            // Generate the form content
            $content = View::make(Theme::getSelected()->getView(Form::VIEWS_DIR . 'form-content.tpl'), array(
                'form' => $this,
                'column' => 0
            ));

            // Wrap the content with the form tag
            return $this->wrap($content);
        }
        catch(\Exception $e){
            App::errorHandler()->exception($e);
        }
    }


    /**
     * Check if the submitted values are correct
     *
     * @param bool $exit If set to true and if the data is not valid,
     *                    this function will output the validation result on HTTP response
     *
     * @return bool true if the data is valid, false else.
     */
    public function check($exit = self::EXIT_JSON){
        if(empty($this->errors)) {
            $this->errors = array();
        }

        foreach($this->inputs as $name => $field){
            $field->check($this);
        }

        if(!empty($this->errors)) {
            $this->status = self::STATUS_ERROR;
            App::logger()->warning(App::session()->getUser()->username . ' has badly completed the form ' . $this->id);
            if($exit) {
                /*** The form check failed ***/
                App::response()->setBody($this->response(self::STATUS_CHECK_ERROR, Lang::get('form.error-fill')));
                throw new AppStopException();
            }
            else{
                $this->addReturn('message', Lang::get('form.error-fill'));
                return false;
            }
        }

        /*** The form check return OK status ***/
        return true;
    }

    /**
     * Register the submitted data in the database
     *
     * @param bool   $exit    If set to true, the script will output after function execution, not depending on the result
     * @param string $success Defines the message to output if the action has been well executed
     * @param string $error   Defines the message to output if an error occured
     *
     * @return mixed The id of the created or updated element in the database
     */
    public function register($exit = self::EXIT_JSON, $success = "", $error = ""){
        try{
            $this->dbaction = self::ACTION_REGISTER;

            if((!isset($this->object) && $this->model == self::DEFAULT_MODEL) || !$this->reference) {
                throw new \Exception("The method register of the class Form can be called only if model and reference properties are set");
            }
            if(!$this->object) {
                $model = $this->model;
                $this->object = new $model($this->getData());
            }
            else{
                $this->object->set($this->reference);
            }


            foreach($this->inputs as $name => $field){
                /* Determine if we have to insert this field in the set of inserted values
                * A field can't be inserted if :
                *   it type is in the independant types
                *   the field is defined as independant
                *   the fiels is defined as no insert
                */
                if(!$field->independant && $field->insert !== false && !$field->disabled) {
                    /*** Insert the field value in the set ***/
                    $this->object->set($name, $field->dbvalue());
                }
            }

            if(!$this->new) {
                $this->object->update();
            }
            else{
                $this->object->save();
            }


            $id = $this->object->getPrimaryColumn();

            $this->addReturn(array(
                'primary' => $this->object->$id,
                'action' => self::ACTION_REGISTER,
                'new' => $this->new
            ));

            $this->status = self::STATUS_SUCCESS;

            App::logger()->info(App::session()->getUser()->username . ' has updated the data on the form ' . $this->id);
            if($exit) {
                // output the response
                App::response()->setBody($this->response(self::STATUS_SUCCESS, $success ? $success : Lang::get('form.success-register')));
                throw new AppStopException();
            }
            return $this->object->$id;
        }
        catch(DBException $e){
            $this->status = self::STATUS_ERROR;
            App::logger()->error('An error occured while registering data on the form ' . $this->id . ' : ' . $e->getMessage());
            if($exit) {
                return $this->response(self::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : ($error ? $error : Lang::get('form.error-register')));
            }
            throw $e;
        }
    }


    /**
     * Delete the element from the database
     *
     * @param bool   $exit    If set to true, the script will output after function execution, not depending on the result
     * @param string $success Defines the message to output if the action has been well executed
     * @param string $error   Defines the message to output if an error occured
     *
     * @return mixed The id of the deleted object
     */
    public function delete($exit = self::EXIT_JSON, $success = "", $error = ""){
        try{
            $this->dbaction = self::ACTION_DELETE;

            if($this->model == self::DEFAULT_MODEL || !$this->reference) {
                throw new \Exception("The method delete of the class Form can be called only if model and reference properties are set");
            }

            if(!$this->object) {
                throw new \Exception("This object instance cannot be removed : No such object");
            }

            $id = $this->object->getPrimaryColumn();
            $this->object->delete();

            $this->addReturn(
                array(
                    'primary' => $this->object->$id,
                    'action' => self::ACTION_DELETE
                )
            );
            $this->status = self::STATUS_SUCCESS;

            App::logger()->info('The delete action on the form ' . $this->id . ' was successflully completed');
            if($exit) {
                App::response()->setBody($this->response(self::STATUS_SUCCESS, $success ? $success : Lang::get('form.success-delete')));
                throw new AppStopException();
            }
            return $this->object->$id;
        }
        catch(DBException $e){
            $this->status = self::STATUS_ERROR;
            App::logger()->error('An error occured while deleting the element of the form ' . $this->id . ' : ' . $e->getMessage());

            if($exit) {
                return $this->response(self::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : ($error ? $error : Lang::get('form.error-delete')));
            }
            throw $e;
        }
    }

    /**
     * Add an error on a field
     *
     * @param string $name  The name of the input to apply the error
     * @param string $error The error message to apply
     */
    public function error($name, $error){
        $this->errors[$name] = $error;
    }


    /**
     * Add data to return to the client. To add several returns in on function call, define the first parameter as an associative array
     *
     * @param string $name    The name of the data to return
     * @param string $message The value to apply
     */
    public function addReturn($name, $message= ""){
        if(is_object($name)) {
            foreach(get_object_vars($name) as $key => $value) {
                $this->addReturn($key, $value);
            }
        }
        elseif(is_array($name)) {
            foreach($name as $key => $value) {
                $this->addReturn($key, $value);
            }
        }
        else{
            $this->returns[$name] = $message;
        }
    }


    /**
     * Return the response of the form (generally when submitted),
     * and set the Response HTTP code corresponding to the response, and the response type as JSON
     *
     * @param string $status  The status to output. You can use the class constants STATUS_SUCCESS, STATUS_CHECK_ERROR or STATUS_ERROR
     * @param string $message The message to output. If not set, the default message corresponding to the status will be output
     *
     * @return array The response result, that will be displayed as json when the script ends
     */
    public function response($status, $message = ''){
        $response = array();
        switch($status){
            case self::STATUS_SUCCESS :
                // The form has been submitted correctly
                App::response()->setStatus(self::HTTP_CODE_SUCCESS);
                if(! $this->nomessage) {
                    $response['message'] = $message ? $message : Lang::get('form.'.$status.'-'.$this->dbaction);
                }
                $response['data'] = $this->returns;
                break;

            case self::STATUS_CHECK_ERROR :
                // An error occured while checking field syntaxes
                App::response()->setStatus(self::HTTP_CODE_CHECK_ERROR);
                $response['message'] = $message ? $message : Lang::get('form.error-fill');
                $response['errors'] = $this->errors;
                break;

            case self::STATUS_ERROR :
            default :
                App::response()->setStatus(self::HTTP_CODE_ERROR);
                $response['message'] = $message ? $message : Lang::get('form.'.$status.'-'.$this->dbaction);
                $response['errors'] = $this->errors;
                break;
        }

        App::response()->setContentType('json');
        return $response;
    }



    /**
     * Make a generic treatment that detect the action to execute, check the form if necessary, and execute the action
     *
     * @param bool $exit If true, exit the script after function execution
     *
     * @return mixed The id of the treated element
     */
    public function treat($exit = self::EXIT_JSON){
        if($this->submitted() == self::ACTION_DELETE) {
            return $this->delete($exit);
        }
        else{
            if($this->check($exit)) {
                return $this->register($exit);
            }
            else{
                return false;
            }
        }
    }
}
