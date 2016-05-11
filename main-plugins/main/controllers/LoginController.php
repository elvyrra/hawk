<?php
/**
 * LoginController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Main;

/**
 * Login controller
 *
 * @package Plugins\Main
 */
class LoginController extends Controller{

    /**
     * Generate the login form
     */
    private function form(){
        /*** Get the registrered login and passwords ***/
        $param = array(
            "id" => "login-form",
            "method" => "post",
            "autocomplete" => false,
            "fieldsets" => array(
                "form" => array(

                    new TextInput(array(
                        "field" => "login",
                        "required" => true,
                        "label" => Lang::get($this->_plugin . '.login-label'),
                    )),

                    new PasswordInput(array(
                        "field" => "password",
                        "required" => true,
                        "get" => true,
                        "label" => Lang::get($this->_plugin . '.login-password-label'),
                    )),
                ),

                "_submits" => array(
                    new SubmitInput(array(
                        "name" => "connect",
                        "value" => Lang::get($this->_plugin . '.connect-button'),
                        'icon' => 'sign-in'
                    )),

                    Option::get($this->_plugin . '.open-register') ?
                        new ButtonInput(array(
                            'name' => 'register',
                            'value' => Lang::get($this->_plugin . '.register-button'),
                            'href' => App::router()->getUri('register'),
                            'target' => 'dialog',
                            'class' => 'btn-success'
                        )) :
                        null,

                    new ButtonInput(array(
                        'name' => 'forgottenPassword',
                        'label' => Lang::get($this->_plugin . '.login-forgotten-password-label'),
                        'href' => App::router()->getUri('forgotten-password'),
                        'target' => 'dialog'
                    ))
                ),
            ),
            'onsuccess' => 'location = app.getUri("index");',
        );

        return new Form($param);
    }

    /**
     * Display the login page
     */
    public function login(){
        $form = $this->form();
        if(!$form->submitted()) {
            if(App::request()->getParams('code') == 403) {
                $form->status = Form::STATUS_ERROR;
                $form->addReturn('message', Lang::get($this->_plugin . '.403-message'));
            }
            // Display the login page in a dialog box
            return Dialogbox::make(array(
                'page' => $form->__toString(),
                'icon' => 'sign-in',
                'title' => Lang::get($this->_plugin . '.login-form-title'),
                // 'width' => '40rem',
            ));
        }
        else{
            if($form->check()) {
                $hash = Crypto::saltHash($form->getData('password'));

                $example = new DBExample(array(
                    '$and' => array(
                        array('email' => $form->getData('login')),
                        array('password' => $hash),
                    )
                ));

                $user = User::getByExample($example);

                if($user) {
                    if(!$user->active) {
                        // The user is not active
                        return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.login-error-inactive-user'));
                    }

                    // The user can be connected
                    App::session()->setData('user', array(
                        'id' => $user->id,
                        'email' => $user->email,
                        'username' => $user->getUsername(),
                        'ip' => App::request()->clientIp()
                    ));

                    if(App::request()->getParams('redirect')) {
                        $form->addReturn('redirect', App::request()->getParams('redirect'));
                    }

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.login-success'));
                }
                else{
                    return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.login-error-authentication'));
                }
            }
        }
    }


    /**
     * Register a new user
     */
    public function register(){
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
                        'encrypt' => array('\Hawk\Crypto', 'saltHash'),
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
            if(!empty($field->roles) && in_array(Option::get('roles.default-role'), $field->roles)) {
                $classname = 'Hawk\\' . ucwords($question->type) . 'Input';
                $field['name'] = $question->name;
                $field['independant'] = true;
                $field['label'] = Lang::get('admin.profile-question-' . $question->name . '-label');

                $param['fieldsets']['profile'][] = new $classname($field);
            }
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
    public function validateRegister(){
        $data = json_decode(Crypto::aes256Decode($this->token), true);
        $data['active'] = 0;

        $user = User::getByExample(new DBExample($data));

        if(!$user) {
            $status = 'danger';
            $messageKey = "main.validate-registration-unknown-error";
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

        App::session()->setData('notification', array(
            'status' => $status,
            'message' => Lang::get($messageKey)
        ));

        App::response()->redirectToAction('index');
    }

    /**
     * Sign-out
     */
    public function logout(){
        session_destroy();

        App::response()->redirectToAction('index');
    }


    /**
     * Display and treat the form when the user forgot his password
     */
    public function forgottenPassword(){
        $form = new Form(array(
            'id' => 'forgotten-password-form',
            'fieldsets' => array(
                'form' => array(
                    new EmailInput(array(
                        'name' => 'email',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.forgotten-pwd-form-email-label')
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
            'onsuccess' => '
                app.dialog(app.getUri("reset-password"));
                app.notify("warning", Lang.get("main.forgotten-pwd-sent-email-message"));
            ',
        ));

        if(!$form->submitted()) {
            Lang::addKeysToJavascript($this->_plugin . '.forgotten-pwd-sent-email-message');

            return Dialogbox::make(array(
                'title' => Lang::get($this->_plugin . '.forgotten-pwd-form-title'),
                'icon' => 'lock-alt',
                'page' => $form
            ));
        }
        else{
            if($form->check()) {
                $user = User::getByEmail($form->getData('email'));

                if(!$user) {
                    // The user does not exists. For security reasons,
                    // reply the email was successfully sent, after a random delay to work around robots
                    usleep(mt_rand(0, 500) * 100);
                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.forgotten-pwd-sent-email-message'));
                }

                try {
                    // The user exists, send an email with a 6 chars random verification code
                    $code = Crypto::generateKey(6);

                    // Register the verification code in the session
                    App::session()->setData('forgottenPassword', array(
                        'email' => $form->getData('email'),
                        'code' => Crypto::aes256Encode($code)
                    ));

                    $mail = new Mail();
                    $mail
                        ->from(Option::get($this->_plugin . '.mailer-from'), Option::get($this->_plugin . '.mailer-from-name'))
                        ->to($form->getData('email'))
                        ->subject(Lang::get($this->_plugin . '.reset-pwd-email-title', array(
                            'sitename' => Option::get($this->_plugin . '.sitename')
                        )))
                        ->title(Lang::get('main.reset-pwd-email-title', array('sitename' => Option::get('main.sitename'))))
                        ->content(View::make(
                            Plugin::current()->getView('reset-password-email.tpl'),
                            array(
                                'sitename' => Option::get($this->_plugin . '.sitename'),
                                'code' => $code
                            )
                        ))
                        ->send();

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.forgotten-pwd-sent-email-message'));

                }
                catch(\Exception $e){
                    return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get($this->_plugin . '.forgotten-pwd-form-error'));
                }

            }
        }
    }

    /**
     * Display and treat the form to reset the user's password
     */
    public function resetPassword(){
        $form = new Form(array(
            'id' => 'reset-password-form',
            'fieldsets' => array(
                'form' => array(
                    new TextInput(array(
                        'name' => 'code',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.reset-pwd-form-code-label')
                    )),
                    new PasswordInput(array(
                        'name' => 'password',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.reset-pwd-form-password-label'),
                        'encrypt' => array('\Hawk\Crypto', 'saltHash')
                    )),
                    new PasswordInput(array(
                        'name' => 'confirmation',
                        'required' => true,
                        'compare' => 'password',
                        'label' => Lang::get($this->_plugin . '.reset-pwd-form-confirmation-label')
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
            'onsuccess' => 'app.dialog(app.getUri("login"));'
        ));

        if(!$form->submitted()) {
            return Dialogbox::make(array(
                'title' => Lang::get($this->_plugin . '.reset-pwd-form-title'),
                'icon' => 'lock-alt',
                'page' => $form
            ));
        }
        else{
            if($form->check()) {
                // Check the verficiation code
                if($form->getData('code') !== Crypto::aes256Decode(App::session()->getData('forgottenPassword.code'))) {
                    $form->error('code', Lang::get($this->_plugin . '.reset-pwd-form-bad-verification-code'));
                    return $form->response(Form::STATUS_CHECK_ERROR);
                }

                try{
                    $user = User::getByEmail(App::session()->getData('forgottenPassword.email'));
                    if($user) {
                        $user->set('password', $form->inputs['password']->dbvalue());
                        $user->save();
                    }
                    else{
                        return $form->response(Form::STATUS_ERROR, App::session()->getData('forgottenPassword.email'));
                    }

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.reset-pwd-form-success'));
                }
                catch(\Exception $e){
                    return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.reset-pwd-form-error'));
                }
            }
        }
    }

}