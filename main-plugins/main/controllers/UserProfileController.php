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
        $user = App::session()->getUser();

        $roles = array_map(function ($role) {
            return $role->getLabel();
        }, Role::getAll('id'));

        $param = array(
            'id' => 'user-profile-form',
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
            if($question->displayInProfile && $question->isAllowedForRole($user->roleId)) {
                $classname = '\Hawk\\' . ucwords($question->type) . 'Input';
                $field = json_decode($question->parameters, true);
                $field['name'] = $question->name;
                $field['id'] = 'user-form-' . $question->name. '-input';
                $field['independant'] = true;
                $field['label'] = Lang::get('admin.profile-question-' . $question->name . '-label');

                if(isset($field['readonly'])){
                    if($field['readonly'])
                        $field['required'] = false;
                }

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
                    if($question->displayInProfile && $question->isAllowedForRole($user->roleId)) {
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

                if($form->getData('email') !== $user->email) {
                    // The user asked to reset it email
                    // Check this email is not used by another user on the application
                    $existingUser = User::getByExample(new DBExample(array(
                        'id' => array(
                            '$ne' => $user->id
                        ),
                        'email' => $form->getData('email')
                    )));

                    if($existingUser) {
                        return $form->response(Form::STATUS_CHECK_ERROR, Lang::get($this->_plugin . '.reset-email-already-used'));
                    }

                    // Send the email to validate the new email

                    // Create the token to validate the new email
                    $tokenData = array(
                        'userId' => $user->id,
                        'currentEmail' => $user->email,
                        'newEmail' => $form->getData('email'),
                        'createTime' => time()
                    );

                    $token = base64_encode(Crypto::aes256Encode(json_encode($tokenData)));

                    // Create the email content
                    $emailContent = View::make($this->getPlugin()->getView('change-email-validation.tpl'), array(
                        'sitename' => Option::get($this->_plugin . '.sitename'),
                        'validationUrl' => App::router()->getUrl('validate-new-email', array(
                            'token' => $token
                        ))
                    ));

                    $email = new Mail();
                    $email  ->to($form->getData('email'))
                        ->from(Option::get('main.mailer-from'), Option::get('main.mailer-from-name'))
                        ->title(Lang::get($this->_plugin . '.reset-email-title', array(
                                'sitename' => Option::get($this->_plugin . '.sitename')
                            )))
                        ->content($emailContent)
                        ->subject(Lang::get($this->_plugin . '.reset-email-title', array(
                                'sitename' => Option::get($this->_plugin . '.sitename')
                            )))
                        ->send();

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.user-profile-update-success-with-email'));
                }

                return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.user-profile-update-success'));
            }
            catch(Exception $e){
                return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.user-profile-update-error'));
            }
        }
    }

    /**
     * Validate the new email for a user
     */
    public function validateNewEmail() {
        $tokenData = json_decode(Crypto::aes256Decode(base64_decode($this->token)), true);

        try {
            if(!$tokenData) {
                // Token format is not valid
                throw new \Exception();
            }

            $user = User::getById($tokenData['userId']);

            if($user->email !== $tokenData['currentEmail']) {
                // Token does not have the correct email corresponding to the user email
                throw new \Exception();
            }

            if($tokenData['createTime'] < time() - 86400) {
                // Token has expired
                throw new \Exception();
            }

            // Everything OK, change the user's email address
            $user->set('email', $tokenData['newEmail']);
            $user->save();

            // Disconnect the user
            session_destroy();

            $status = 'success';
            $messageKey = 'main.reset-email-success';
        }
        catch(\Exception $e) {
            $messageKey = 'main.reset-email-invalid-token';
            $status = 'error';
        }

        $this->addJavaScriptInline('
            require(["app"], function(){
                app.notify("' . $status . '", "' . addcslashes(Lang::get($messageKey), '"') . '");
            });'
        );

        return MainController::getInstance()->main();
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