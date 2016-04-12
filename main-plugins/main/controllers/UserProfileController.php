<?php
/**
 * UserProfileController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Main;

/**
 * User profile controller
 *
 * @package Plugins\Main
 */
class UserProfileController extends Controller{

    /**
     * Create or edit an user
     */
    public function edit(){
        if(!$this->userId) {
            $user = Session::getUser();
        }
        else{
            $user = User::getById($this->userId);
        }
        $roles = array_map(function ($role) {
            return $role->getLabel();
        }, Role::getAll('id'));

        $param = array(
            'id' => 'user-form',
            'upload' => true,
            'object' => $user,
            'fieldsets' => array(
                'general' => array(
                    'legend' => Lang::get('admin.user-form-general-legend'),

                    new TextInput(array(
                        'name' => 'username',
                        'required' => true,
                        'label' => Lang::get('admin.user-form-username-label'),
                        'disabled' => true,
                    )),

                    new EmailInput(array(
                        'name' => 'email',
                        'required' => true,
                        'label' => Lang::get('admin.user-form-email-label'),
                        'disabled' => true,
                    ))
                ),

                'profile' => array(
                    'legend' => Lang::get('admin.user-form-profile-legend'),
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get($this->_plugin . '.valid-button')
                    ))
                ),
            ),

            'onsuccess' => 'app.dialog("close")',
        );

        // Get the user profile questions
        $questions = ProfileQuestion::getAll('name', array(), array('order' => DB::SORT_ASC));

        // Generate the question fields
        foreach($questions as $question){
            if($question->displayInProfile && ProfileQuestion::allowToRole($question->name, $user->roleId)){
                $classname = '\Hawk\\' . ucwords($question->type) . 'Input';
                $field = json_decode($question->parameters, true);
                $field['name'] = $question->name;
                $field['id'] = 'user-form-' . $question->name. '-input';
                $field['independant'] = true;
                $field['label'] = Lang::get('admin.profile-question-' . $question->name . '-label');

                if($question->editable == 0)
                    $field['readonly'] = true;

                if($user) {
                    if($question->type == "file") {
                        $field['after'] = sprintf(
                            '<img src="%s" class="profile-image" />',
                            $user->getProfileData($question->name) ? $user->getProfileData($question->name) : ''
                        );
                    }
                    else{
                        $field['default'] = $user->getProfileData($question->name);
                    }
                }

                if($question->name == 'language') {
                    // Get language options
                    $languages = Language::getAllActive();
                    $options = array();
                    foreach($languages as $language){
                        $options[$language->tag] = $language->label;
                    }
                    $field['options'] = $options;
                    if(!$field['default']) {
                        $field['default'] = Option::get($this->_plugin . '.language');
                    }
                }


                $param['fieldsets']['profile'][] = new $classname($field);
            }
        }

        $form = new Form($param);
        if(!$form->submitted()) {

            return NoSidebarTab::make(array(
                'title' => Lang::get('admin.user-form-title'),
                'page' => array(
                    'content' => $form
                )
            ));
        }
        else{
            try{
                foreach($questions as $question){
                    if($question->displayInProfile && ProfileQuestion::allowToRole($question->name, $user->roleId)){
                        if($question->type === 'file') {
                            $upload = Upload::getInstance($question->name);

                            if($upload) {
                                $file = $upload->getFile(0);
                                $dir = Plugin::current()->getPublicUserfilesDir()  . 'img/';
                                $url = Plugin::current()->getUserfilesUrl() . 'img/';
                                if(!is_dir($dir)) {
                                    mkdir($dir, 0755, true);
                                }

                                $basename = uniqid() . $file->extension;
                                $upload->move($file, $dir, $basename);
                                $user->setProfileData($question->name, $url . $basename);
                            }
                        }
                        else{
                            $user->setProfileData($question->name, $form->inputs[$question->name]->dbvalue());
                        }
                    }
                }

                $user->saveProfile();
                return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.user-profile-update-success'));
            }
            catch(Exception $e){
                return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.user-profile-update-error'));
            }
        }

    }

    /**
     * Change the current user password
     */
    public function changePassword(){
        $params = array(
            'id' => 'update-password-form',
            'fieldsets' => array(
                'form' => array(
                    new PasswordInput(array(
                        'name' => 'current-password',
                        'label' => Lang::get($this->_plugin . '.update-password-current-password-label'),
                        'required' => true,
                    )),

                    new PasswordInput(array(
                        'name' => 'new-password',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.update-password-new-password-label'),
                    )),

                    new PasswordInput(array(
                        'name' => 'password-confirm',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.update-password-new-password-confirm-label'),
                        'compare' => 'new-password'
                    ))
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get($this->_plugin . '.valid-button'),
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get($this->_plugin . '.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                ),

            ),
            'onsuccess' => 'app.dialog("close")'
        );

        $form = new Form($params);

        if(!$form->submitted()) {
            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'title' => Lang::get($this->_plugin . '.update-password-title'),
                'icon' => 'lock',
                'page' => $form
            ));
        }
        else{
            if($form->check()) {
                $me = Session::getUser();
                if($me->password != Crypto::saltHash($form->getData('current-password'))) {
                    return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.update-password-bad-current-password'));
                }
                try{
                    $me->set('password', Crypto::saltHash($form->getData('new-password')));
                    $me->save();

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.update-password-success'));
                }
                catch(Exception $e){
                    return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get($this->_plugin . '.update-password-error'));
                }

            }
        }

    }
}