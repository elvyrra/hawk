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
            'id' => 'login-form',
            'method' => 'post',
            'autocomplete' => false,
            'fieldsets' => array(
                'form' => array(
                    new TextInput(array(
                        'field' => 'login',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.login-label'),
                    )),

                    new PasswordInput(array(
                        'field' => 'password',
                        'required' => true,
                        'get' => true,
                        'label' => Lang::get($this->_plugin . '.login-password-label'),
                        'pattern' => PasswordInput::LEAK_PATTERN
                    )),
                ),

                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'connect',
                        'value' => Lang::get($this->_plugin . '.connect-button'),
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
            'onsuccess' => '
                if(data.redirect) {
                    location.hash = "#!" + data.redirect;
                }

                location.reload();
            '
        );

        return new Form($param);
    }

    /**
     * Display the login page
     */
    public function login() {
        $form = $this->form();
        if(!$form->submitted()) {
            // Display the login page in a dialog box
            return Dialogbox::make(array(
                'page' => $form->__toString(),
                'icon' => 'sign-in',
                'title' => Lang::get($this->_plugin . '.login-form-title'),
            ));
        }
        else{
            if($form->check()) {
                $user = User::getByEmail($form->getData('login'));

                if(!$user) {
                    $user = User::getByUsername($form->getData('login'));
                }

                if($user) {
                    if(!$user->checkPassword($form->getData('password'))) {
                        if($user->password === Crypto::saltHash($form->getData('password'))) {
                            // Update the user password with the new hash system
                            $user->password = Crypto::hashPassword($form->getData('password'));
                            $user->save();
                        }
                        else {
                            return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.login-error-authentication'));
                        }
                    }

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
     * Sign-out
     */
    public function logout(){
        session_destroy();

        App::response()->redirectToRoute('index');
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
            $this->addKeysToJavascript($this->_plugin . '.forgotten-pwd-sent-email-message');

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
    public function resetPassword() {
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
                        'encrypt' => array('\Hawk\Crypto', 'hashPassword')
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