<?php

namespace Hawk\Plugins\Main;

/**
 * This controller manages the registrations
 */
class RegisterController extends Controller {
    /**
     * Register a new user
     */
    public function register() {
        $param = array(
            'id' => 'register-form',
            'model' => 'User',
            'reference' => array('id' => -1),
            'fieldsets' => array(
                'global' => array(
                    'legend' => Lang::get($this->_plugin . '.register-connection-legend'),

                    new TextInput(array(
                        'name' => 'username',
                        'required' => true,
                        'unique' => true,
                        'pattern' => '/^\w+$/',
                        'label' => Lang::get($this->_plugin . '.register-username-label')
                    )),

                    new EmailInput(array(
                        'name' => 'email',
                        'required' => true,
                        'unique' => true,
                        'label' => Lang::get($this->_plugin . '.register-email-label'),
                    )),

                    new PasswordInput(array(
                        'name' => 'password',
                        'required' => true,
                        'encrypt' => array('\Hawk\Crypto', 'hashPassword'),
                        'label' => Lang::get($this->_plugin . '.register-password-label')
                    )),

                    new PasswordInput(array(
                        'name' => 'passagain',
                        'required' => true,
                        'independant' => true,
                        'label' => Lang::get($this->_plugin . '.register-passagain-label'),
                        'compare' => 'password'
                    ))
                ),

                'profile' => array(
                    'legend' => Lang::get($this->_plugin . '.register-profile-legend')
                ),

                'terms' => array(
                    Option::get($this->_plugin . '.confirm-register-terms') ?
                        new CheckboxInput(array(
                            'name' => 'terms',
                            'required' => true,
                            'independant' => true,
                            'labelWidth' => 'auto',
                            'label' => Lang::get($this->_plugin . '.register-terms-label', array(
                                'uri' => App::router()->getUri('terms')
                            )),
                        )) :
                        null
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get($this->_plugin . '.register-button')
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get($this->_plugin . '.cancel-button'),
                        'href' => App::router()->getUri('login'),
                        'target' => 'dialog',
                    ))
                )
            ),

            'onsuccess' => 'app.dialog(app.getUri("login"))',
        );

        $questions = ProfileQuestion::getRegisterQuestions();

        foreach($questions as $question){
            $field = json_decode($question->parameters, true);

            if($question->isAllowedForRole(Option::get('roles.default-role'))) {
                $classname = 'Hawk\\' . ucwords($question->type) . 'Input';
                $field['name'] = $question->name;
                $field['independant'] = true;
                $field['label'] = Lang::get('admin.profile-question-' . $question->name . '-label');

                // At register, no field is readonly!
                $field['readonly'] = false;
                $param['fieldsets']['profile'][] = new $classname($field);
            }
        }

        if(count($param['fieldsets']['profile']) === 1) {
            unset($param['fieldsets']['profile']);
        }



        $form = new Form($param);
        if(!$form->submitted()) {
            return Dialogbox::make(array(
                'page' => $form->__toString(),
                'icon' => 'sign-in',
                'title' => Lang::get($this->_plugin . '.login-form-title'),
                'width' => '50rem',
            ));
        }
        else{
            if($form->check()) {
                try{
                    $user = new User(array(
                        'username' => $form->inputs['username']->dbvalue(),
                        'email' => $form->inputs['email']->dbvalue(),
                        'password' => $form->inputs['password']->dbvalue(),
                        'active' => Option::get($this->_plugin . '.confirm-register-email') ? 0 : 1,
                        'createTime' => time(),
                        'createIp' => App::request()->clientIp(),
                        'roleId' => Option::get('roles.default-role'),
                    ));

                    $user->save();

                    foreach($questions as $question){
                        if($question->type === 'file') {
                            $upload = Upload::getInstance($question->name);

                            if($upload) {
                                $file = $upload->getFile(0);
                                $dir = Plugin::current()->getUserfilesDir()  . 'img/';
                                $url = Plugin::current()->getUserfilesUrl() . 'img/';
                                if(!is_dir($dir)) {
                                    mkdir($dir, 0755, true);
                                }

                                $upload->move($file, $dir);
                                $user->setProfileData($question->name, $url . $file->basename);
                            }
                        }
                        else{
                            $user->setProfileData($question->name, $form->inputs[$question->name]->dbvalue());
                        }
                    }

                    $user->saveProfile();

                    if(Option::get($this->_plugin . '.confirm-register-email')) {
                        // Send an email to validate the registration
                        $tokenData = array(
                            'username' => $user->username,
                            'email' => $user->email,
                            'createTime' => $user->createTime,
                            'createIp' => $user->createIp
                        );
                        $token = Crypto::aes256Encode(json_encode($tokenData));
                        $url = App::router()->getUrl('validate-registration', array('token' => $token));


                        $data = array(
                            'themeBaseCss' => Theme::getSelected()->getBaseCssUrl(),
                            'themeCustomCss' => Theme::getSelected()->getCustomCssUrl(),
                            'logoUrl' =>  Option::get($this->_plugin . '.logo') ?
                                Plugin::current()->getUserfilesUrl(Option::get($this->_plugin . '.logo')) :
                                Plugin::current()->getStaticUrl('img/hawk-logo.png'),
                            'sitename' => Option::get($this->_plugin . '.sitename'),
                            'url' => $url
                        );
                        if(Option::get($this->_plugin . '.confirm-email-content')) {
                            $mailContent = View::makeFromString(Option::get($this->_plugin . '.confirm-email-content'), $data);
                        }
                        else{
                            $mailContent = View::make(Plugin::current()->getView('registration-validation-email.tpl'), $data);
                        }

                        $mail = new Mail();
                        $mail->from(Option::get($this->_plugin . '.mailer-from'))
                            ->fromName(Option::get($this->_plugin . '.mailer-from-name'))
                            ->to($user->email)
                            ->title(Lang::get('main.register-email-title', array('sitename' => Option::get('main.sitename'))))
                            ->content($mailContent)
                            ->subject(Lang::get($this->_plugin . '.register-email-title', array('sitename' => Option::get($this->_plugin . '.sitename'))))
                            ->send();

                        return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.register-send-email-success'));
                    }
                    else{
                        // validate the registration
                        return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.register-success'));
                    }
                }
                catch(Exception $e){
                    return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get($this->_plugin . '.register-error'));
                }
            }
        }
    }


    /**
     * Validate registration
     */
    public function validateRegistration() {
        $data = json_decode(Crypto::aes256Decode($this->token), true);
        $data['active'] = 0;

        $user = User::getByExample(new DBExample($data));

        if(!$user) {
            $status = 'danger';
            $messageKey = $this->_plugin . '.validate-registration-unknown-error';
        }
        else{
            try{
                $user->set('active', 1);
                $user->save();

                $status = 'success';
                $messageKey = $this->_plugin . '.register-success';
            }
            catch(Exception $e){
                $status = 'danger';
                $messageKey = $this->_plugin . '.validate-registration-error';
            }
        }

        return array(
            'status' => $status,
            'message' => Lang::get($messageKey)
        );
    }


    /**
     * Validate the registartion that has been created by an administrator. Display a form to set the user password, and
     * activate the account
     */
    public function validateAdminRegistration() {
        $data = json_decode(Crypto::aes256Decode($this->token), true);
        $data['active'] = 0;

        $user = User::getByExample(new DBExample($data));

        $error = null;
        if(!$user) {
            $error = Lang::get($this->_plugin . '.validate-registration-unknown-error');
        }

        if($error && App::request()->method() === 'get') {
            App::session()->setData('notification', array(
                'status' => 'error',
                'message' => $error
            ));

            App::response()->redirectToRoute('index');
            return;
        }

        // The page is open in a dialog box
        $form = new Form(array(
            'id' => 'set-first-password-form',
            'fieldsets' => array(
                'form' => array(
                    new PasswordInput(array(
                        'name' => 'password',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.set-first-pwd-form-password-label'),
                        'encrypt' => array('\Hawk\Crypto', 'hashPassword')
                    )),
                    new PasswordInput(array(
                        'name' => 'confirmation',
                        'required' => true,
                        'compare' => 'password',
                        'label' => Lang::get($this->_plugin . '.set-first-pwd-form-confirmation-label')
                    ))
                ),
                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'label' => Lang::get($this->_plugin . '.valid-button')
                    )),
                    new ButtonInput(array(
                        'name' => 'cancel',
                        'label' => Lang::get($this->_plugin . '.cancel-button'),
                        'href' => App::router()->getUri('login'),
                        'target' => 'dialog'
                    ))
                )
            ),
            'onsuccess' => 'location = app.getUri("index");'
        ));

        if(!$form->submitted()) {
            $page = View::make($this->getPlugin()->getView('set-first-password.tpl'), array(
                'form' => $form
            ));


            return MainController::getInstance()->main($page);
        }
        else {
            if($error) {
                return $form->response(Form::STATUS_ERROR, $error);
            }

            if($form->check()) {
                try{
                    $user->password = $form->inputs['password']->dbvalue();
                    $user->active = 1;
                    $user->save();

                    // Create a connection for the new user
                    App::session()->setData('user', array(
                        'id' => $user->id,
                        'email' => $user->email,
                        'username' => $user->getUsername(),
                        'ip' => App::request()->clientIp()
                    ));

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.set-first-pwd-form-success'));
                }
                catch(\Exception $e){
                    return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.set-first-pwd-form-error'));
                }
            }
        }
    }
}