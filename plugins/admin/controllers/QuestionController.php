<?php
/**
 * QuestionController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Questions controller
 *
 * @package Plugins\Admin
 */
class QuestionController extends Controller{
    /**
     * Display the list of the profile questions
     */
    public function listQuestions(){
        // Get all ProfileQuestions
        $questions = ProfileQuestion::getAll();

        // Get all Roles
        $roles = Role::getAll();

        // Create parameters for form
        $param = array(
            'id' => 'display-questions-form',
            'action' => App::router()->getUri('profile-questions'),
            'fieldsets' => array(
                'form' => array(),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button'),
                    )),

                    new ButtonInput(array(
                        'name' => 'new-question',
                        'value' => Lang::get($this->_plugin . '.new-question-btn'),
                        'class' => 'btn-success',
                        'href' => App::router()->getUri('edit-profile-question', array('name' => '_new')),
                        'target' => 'dialog',
                        'icon' => 'plus'
                    ))
                )
            )
        );

        // For each ProfileQuestion add roles, displayInRegister and displayInProfile
        foreach($questions as $question){
            // Add the input to display in register form
            $param['fieldsets']['form'][] = new CheckboxInput(array(
                'name' => 'register-display[' . $question->name . ']',
                'default' => $question->displayInRegister,
                'nl' => false
            ));

            // Add the input to display in the user profile
            $param['fieldsets']['form'][] = new CheckboxInput(array(
                'name' => 'profile-display[' . $question->name . ']',
                'default' => $question->displayInProfile,
                'nl' => false
            ));

            // Get roles associate to this ProfileQuestion in json parameters
            $attributesRoles = ProfileQuestion::getByName($question->name)->getRoles();

            // For each roles create a Checkbox
            foreach($roles as $role){
                // Add the input to display in the user profile
                $param['fieldsets']['form'][] = new CheckboxInput(array(
                    'name' => 'role[' . $question->name . '][' . $role->name . ']',
                    'default' => in_array($role->id, $attributesRoles) ? 1 : 0,
                    'nl' => false
                ));
            }
        }

        // Create form
        $form = new Form($param);

        // Create parameters for the list to display
        $paramList = array(
            'id' => 'profile-questions-list',
            'model' => 'ProfileQuestion',
            'action' => App::router()->getUri('profile-questions'),
            'lines' => 'all',
            'navigation' => false,
            'sort' => array('order' => DB::SORT_ASC),
            'fields' => array(
                'name' => array(
                    'hidden' => true
                ),
                'editable' => array(
                    'hidden' => true
                ),
                'actions' => array(
                    'independant' => true,
                    'display' => function ($value, $field, $line) {
                        if($line->editable) {
                            return
                                Icon::make(array(
                                    'icon' => 'pencil',
                                    'class' => 'text-info',
                                    'href' => App::router()->getUri('edit-profile-question', array('name' => $line->name)),
                                    'target' => 'dialog',
                                    'title' => Lang::get($this->_plugin . '.edit-profile-question')
                                )) .

                                Icon::make(array(
                                    'icon' => 'times',
                                    'class' => 'text-danger delete-question',
                                    'data-question' => $line->name,
                                    'title' => Lang::get($this->_plugin . '.delete-profile-question')
                                ));
                        }
                        else{
                            return '';
                        }
                    },
                    'sort' => false,
                    'search' => false,
                ),
                'label' => array(
                    'independant' => true,
                    'display' => function ($value, $field, $line) {
                        return Lang::get($this->_plugin . ".profile-question-$line->name-label") . " ( $line->name )";
                    },
                    'sort' => false,
                    'search' => false,
                ),
                'displayInRegister'=> array(
                    'label' => Lang::get($this->_plugin . ".list-questions-register-visible-label"),
                    'sort' => false,
                    'search' => false,
                    'display' => function ($value, $field, $line) use ($form) {
                        return $form->inputs['register-display[' . $line->name . ']'];
                    }
                ),
                'displayInProfile' => array(
                    'label' => Lang::get($this->_plugin . '.list-questions-profile-visible-label'),
                    'sort' => false,
                    'search' => false,
                    'display' => function ($value, $field, $line) use ($form) {
                        return $form->inputs['profile-display[' . $line->name . ']'];
                    }
                )
            ),
        );

        // For each roles create a checkbox by line profileQuestion!
        foreach($roles as $role){
            // Add the input to display in register form
            $paramList['fields'][$role->name] = array(
                'independant' => true,
                'label' => $role->getLabel(),
                'search' => false,
                'sort' => false,
                'display' => function ($value, $field, $line) use ($form) {
                    return $form->inputs['role[' . $line->name . '][' . $field->name . ']'];
                },
            );
        }

        // Create List
        $list = new ItemList($paramList);

        if(!$form->submitted()) {
            if($list->isRefreshing()) {
                return $list->display();
            }

            $this->addKeysToJavaScript($this->_plugin . ".confirm-delete-question");
            $content = View::make(Plugin::current()->getView("questions-list.tpl"), array(
                'list' => $list,
                'form' => $form
            ));

            return $form->wrap($content);
        }

        // Extract from form, all infos abour roles associate to ProfileQuestion
        $listRoles = array();
        $roles = Role::getAll('name');
        $save = array();

        foreach($questions as $question) {
            $question->displayInRegister = (bool) $form->inputs['register-display[' . $question->name . ']']->value;

            $question->displayInProfile = (bool) $form->inputs['profile-display[' . $question->name . ']']->value;

            $params = json_decode($question->parameters);
            if(!$params) {
                $params = new \stdClass;
            }
            $params->roles = array();

            foreach($roles as $role) {
                if($form->inputs['role[' . $question->name . '][' . $role->name . ']']->value) {
                    $params->roles[] = $role->id;
                }
            }

            $question->parameters = json_encode($params);

            $question->update();
        }

        return $form->response(Form::STATUS_SUCCESS);
    }

    /**
     * Edit a profile question
     */
    public function edit(){
        $q = ProfileQuestion::getByName($this->name);
        $roles = Role::getAll();

                // Get roles associate to this ProfileQuestion in json parameters
        if($q)
            $attributesRoles = $q->getRoles();
        else
            $attributesRoles = array();

        $allowedTypes = ProfileQuestion::$allowedTypes;
        $param = array(
            'id' => 'profile-question-form',
            'model' => 'ProfileQuestion',
            'reference' => array('name' => $this->name),
            'labelWidth' => '200px',
            'fieldsets' => array(
                'general' => array(
                    'legend' => Lang::get($this->_plugin . '.profile-question-form-general-legend'),

                    new TextInput(array(
                        'name' => 'name',
                        'unique' => true,
                        'maxlength' => 32,
                        'label' =>  Lang::get($this->_plugin . '.profile-question-form-name-label') . ' ' .
                                    Lang::get($this->_plugin . '.profile-question-form-name-description'),
                        'required' => true,
                    )),

                    new SelectInput(array(
                        'name' => 'type',
                        'required' => true,
                        'options' => array_combine($allowedTypes, array_map(function ($type) {
                            return Lang::get($this->_plugin . '.profile-question-form-type-' . $type);
                        }, $allowedTypes)),
                        'label' => Lang::get($this->_plugin . '.profile-question-form-type-label'),
                        'attributes' => array(
                            'e-value' => 'type',
                        )
                    )),

                    new CheckboxInput(array(
                        'name' => 'displayInRegister',
                        'label' => Lang::get($this->_plugin . '.profile-question-form-displayInRegister-label')
                    )),

                    new CheckboxInput(array(
                        'name' => 'displayInProfile',
                        'label' => Lang::get($this->_plugin . '.profile-question-form-displayInProfile-label')
                    )),

                    new HiddenInput(array(
                        'name' => 'editable',
                        'value' => 1,
                    )),
                ),

                'parameters' => array(
                    'legend' => Lang::get($this->_plugin . '.profile-question-form-parameters-legend'),

                    new ObjectInput(array(
                        'name' => 'parameters',
                        'id' => 'question-form-parameters',
                        'hidden' => true,
                        'attributes' => array(
                            'e-value' => 'parameters'
                        )
                    )),

                    new CheckboxInput(array(
                        'name' => 'required',
                        'independant' => true,
                        'label' => Lang::get($this->_plugin . '.profile-question-form-required-label'),
                        'attributes' => array(
                            'e-value' => "required",
                        )
                    )),

                    new CheckboxInput(array(
                        'name' => 'readonly',
                        'independant' => true,
                        'label' => Lang::get($this->_plugin . '.profile-question-form-readonly-label'),
                        'attributes' => array(
                            'e-value' => "readonly",
                        )
                    )),

                    new DatetimeInput(array(
                        'name' => 'minDate',
                        'independant' => true,
                        'label' => Lang::get($this->_plugin . '.profile-question-form-minDate-label'),
                        'attributes' => array(
                            'e-value' => "minDate"
                        ),
                    )),

                    new DatetimeInput(array(
                        'name' => 'maxDate',
                        'independant' => true,
                        'label' => Lang::get($this->_plugin . '.profile-question-form-maxDate-label'),
                        'attributes' => array(
                            'e-value' => "maxDate"
                        ),
                    )),

                    new HtmlInput(array(
                        'name' => 'parameters-description',
                        'value' => '<p class="alert alert-info">' .
                                        Icon::make(array(
                                            'icon' => 'exclamation-circle'
                                        )) .
                                        Lang::get($this->_plugin . '.profile-question-form-translation-description') .
                                    '</p>'
                    )),

                    new TextInput(array(
                        'name' => 'label',
                        'required' => true,
                        'independant' => true,
                        'label' => Lang::get($this->_plugin . '.profile-question-form-label-label'),
                        'default' => $this->name != '_new' ? Lang::get($this->_plugin . '.profile-question-' . $this->name . '-label') : ''
                    )),

                    new TextareaInput(array(
                        'name' => 'options',
                        'independant' => true,
                        'required' => App::request()->getBody('type') == 'select' || App::request()->getBody('type') == 'radio',
                        'label' =>  Lang::get($this->_plugin . '.profile-question-form-options-label') . '<br />' .
                                    Lang::get($this->_plugin . '.profile-question-form-options-description'),
                        'labelClass' => 'required',
                        'attributes' => array(
                            'e-value' => "options",
                        ),
                        'cols' => 20,
                        'rows' => 10
                    ))
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),

                    new DeleteInput(array(
                        'name' => 'delete',
                        'value' => Lang::get('main.delete-button'),
                        'notDisplayed' => $this->name == '_new'
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                )

            ),
            'onsuccess' => 'app.dialog("close"); app.load(app.getUri("profile-questions"), {selector : "#admin-questions-tab"})',
        );

        $form = new Form($param);

        if(!$form->submitted()) {
            $this->addJavaScript($this->getPlugin()->getJsUrl('question-form.js'));

            $content = View::make(Plugin::current()->getView("question-form.tpl"), array(
                'form' => $form
            ));

            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'title' => Lang::get($this->_plugin . ".users-questions-title"),
                'icon' => 'file-word-o',
                'page' => $content
            ));
        }
        else{
            if($form->submitted() == "delete") {
                $this->delete();

                return $form->response(Form::STATUS_SUCCESS);
            }
            else{
                if($form->check()) {
                    $form->register(Form::NO_EXIT);

                    Language::current()->saveTranslations(array(
                        'admin' => array(
                            'profile-question-' . $form->getData("name") . '-label' => App::request()->getBody('label')
                        )
                    ));

                    // Create the lang options
                    if($form->inputs['options']->required) {
                        $keys = array('admin'=> array());
                        foreach(explode(PHP_EOL, $form->getData("options")) as $i => $option){
                            if(!empty($option)) {
                                $keys['admin']['profile-question-' . $form->getData("name") . '-option-' . $i] = trim($option);
                            }
                        }
                        Language::current()->saveTranslations($keys);
                    }

                    return $form->response(Form::STATUS_SUCCESS);
                }
            }
        }
    }


    /**
     * Delete a profile question
     */
    public function delete(){
        $question = ProfileQuestion::getByName($this->name);

        if($question->editable) {
            $params = json_decode($question->parameters, true);

            $question->delete();

            // Remove the language keys for the label and the options
            $keysToRemove = array(
                'admin' => array(
                    'profile-question-' . $this->name . '-label',
                )
            );

            if(!empty($params['options'])) {
                foreach($params['options'] as $i => $value){
                    $keysToRemove['admin'][] = 'profile-question-' . $this->name . '-option-' . $i;
                }
            }
            foreach(Language::getAll() as $language){
                $language->removeTranslations($keysToRemove);
            }
        }
    }
}