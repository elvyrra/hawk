<?php

	
class LoginController extends Controller{
	
	/*_______________________________________________________
	
				Generate the login form
	_______________________________________________________*/	
	private function form(){
		/*** Get the registrered login and passwords ***/
		$login = !empty($_COOKIE[sha1('login')]) ? Crypto::aes256Decode($_COOKIE[sha1('login')]) : '';
		$password = !empty($_COOKIE[sha1('password')]) ? $_COOKIE[sha1('password')] : '';

		$param = array(
			"id" => "login-form",
			"method" => "post",
			"autocomplete" => false,
			"action" => Router::getUri('LoginController.login'),
			"fieldsets" => array(
				"form" => array(
									
					new TextInput(array(
						"field" => "login",
						"required" => true,
						"default" => $login,
						"label" => Lang::get('main.login-label'),						
					)),
					
					new PasswordInput(array(
						"field" => "password",
						"required" => true,						
						"default" => $password,
						"decrypt" => array('Crypto', 'aes256Decode'),
						"get" => true,						
						"label" => Lang::get('main.login-password-label'),
					)),
					
					new CheckboxInput(array(
						"field" => "remember",
						"independant" => true,
						"default" => $login || $password ? 1 : 0,
						"beforeLabel" => true,
						"label" => Lang::get('main.login-remember-label'),
					)),		
				),

				"_submits" => array(
					new SubmitInput(array(
						"name" => "connect",
						"value" => Lang::get('main.connect-button'),						
					)),
					
					Option::get('main.open-register') ? 
						new ButtonInput(array(
							'name' => 'register',
							'value' => Lang::get('main.register-button'),
							'href' => Router::getUri('LoginController.register'),
							'target' => 'dialog',
							'class' => 'btn-primary'
						)) : 
						null
				),
			),
			'onsuccess' => 'location = app.getUri("index");',
		);	

		return new Form($param);
	}
	
	/*_______________________________________________________
	
				Display the login page 
	_______________________________________________________*/
	public function login(){
		$form = $this->form();
		if(!$form->submitted()){	
			// Display the login page in a dialog box
			return View::make($this->theme->getView('dialogbox.tpl'), array(
				'page' => $form->__toString(),
				'icon' => 'sign-in',
				'title' => Lang::get('main.login-form-title'),
				'width' => '400px',
			));
		}
		else{
			if($form->check()){
				$hash = Crypto::saltHash($form->getData('password'));
				
				$example = new DBExample(array(
					'$and' => array(
						'$or' => array(
							array('email' => $form->getData('login')),
							array('username' => $form->getData('login'))
						),						
						array('password' => $hash),						
					)					
				));

				$user = User::getByExample($example);
					
				if($user){					
					if(!$user->active){
						// The user is not active					
						$form->response(Form::STATUS_ERROR, Lang::get('main.login-error-inactive-user'));
					}
					
					// The user can be connected 
					$_SESSION['user'] = array(
						'id' => $user->id,						
						'email' => $user->email,
						'username' => $user->getUsername(),
						'ip' => Request::clientIp()
					);					
					if(isset($_POST['remember'])){
						setcookie(sha1("login"), Crypto::aes256Encode($form->getData('login')), time() + 3600 * 24 *7, '/');
						setcookie(sha1("password"), Crypto::aes256Encode($form->getData('password')), time() + 3600 * 24 *7, '/');
					}
					
					$form->response(Form::STATUS_SUCCESS, Lang::get('main.login-success'));
				}
				else{					
					$form->response(Form::STATUS_ERROR, Lang::get('main.login-error-authentication'));
				}			
			}			
		}
	}	
	
	public function register(){
		$param = array(
			'id' => 'register-form',
			'model' => 'User',
			'reference' => array('id' => -1),
			'fieldsets' => array(
				'global' => array(
					'legend' => Lang::get('main.register-connection-legend'),

					new TextInput(array(
						'name' => 'username',						
						'required' => true,
						'unique' => true,
						'pattern' => '/^\w+$/',
						'label' => Lang::get('main.register-username-label')
					)),

					new EmailInput(array(
						'name' => 'email',
						'required' => true,
						'unique' => true,
						'label' => Lang::get('main.register-email-label'),
					)),

					new PasswordInput(array(
						'name' => 'password',
						'required' => true,
						'encrypt' => array('Crypto', 'saltHash'),
						'label' => Lang::get('main.register-password-label')
					)),

					new PasswordInput(array(
						'name' => 'passagain',
						'required' => true,
						'independant' => true,
						'label' => Lang::get('main.register-passagain-label'),
						'compare' => 'password'
					))
				),

				'profile' => array(
					'legend' => Lang::get('main.register-profile-legend')
				),

				'terms' => array(
					Option::get('main.confirm-register-terms') ? 
						new CheckboxInput(array(
							'name' => 'terms',
							'required' => true,
							'independant' => true,
							'labelWidth' => 'auto',
							'label' => Lang::get('main.register-terms-label', array('uri' => Router::getUri('terms'))),
						)) :
						null
				),

				'_submits' => array(					
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.register-button')
					)),

					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'href' => Router::getUri('login'),
						'target' => 'dialog',
					))
				)
			),
			
			'onsuccess' => 'app.dialog(app.getUri("login"))',
		);

		$questions = ProfileQuestion::getRegisterQuestions();
		foreach($questions as $question){
			$classname = ucwords($question->type) . 'Input';
            $field = json_decode($question->parameters, true);
            $field['name'] = $question->name;
            $field['independant'] = true;
            $field['label'] = Lang::get('admin.profile-question-' . $question->name . '-label');
            
            $param['fieldsets']['profile'][] = new $classname($field);

            if($question->type === 'file'){
            	$param['upload'] = true;
            }
       	}
		
		$form = new Form($param);
		if(!$form->submitted()){
			return View::make($this->theme->getView('dialogbox.tpl'), array(
				'page' => $form->__toString(),
				'icon' => 'sign-in',
				'title' => Lang::get('main.login-form-title'),
				'width' => '450px',
			));
		}
		else{	
			if($form->check()){
				try{
					$user = new User(array(
						'username' => $form->fields['username']->dbvalue(),
						'email' => $form->fields['email']->dbvalue(),
						'password' => $form->fields['password']->dbvalue(),
						'active' => Option::get('main.confirm-register-email') ? 0 : 1,
						'createTime' => time(),
						'createIp' => Request::clientIp(),
						'roleId' => Option::get('roles.default-role'),
					));

					$user->save();

					foreach($questions as $question){
	                    if($question->type === 'file'){
	                        $upload = Upload::getInstance($question->name);

	                        if($upload){
	                            $file = $upload->getFile(0);
	                            $dir = Plugin::current()->getUserfilesDir()  . 'img/';
	                            $url = Plugin::current()->getUserfilesUrl() . 'img/';
	                            if(!is_dir($dir)){
	                                mkdir($dir, 0755, true);
	                            }
	                            
	                            $upload->move($file, $dir);
	                            $user->setProfileData($question->name, $url . $file->basename);
	                        }
	                    }
	                    else{
	                        $user->setProfileData($question->name, $form->fields[$question->name]->dbvalue());
	                    }
	                }            

                	$user->saveProfile();

					if(Option::get('main.confirm-register-email')){
						// Send an email to validate the registration
						$tokenData = array(
							'username' => $user->username,
							'email' => $user->email,
							'createTime' => $user->createTime,
							'createIp' => $user->createIp
						);
						$token = Crypto::aes256Encode(json_encode($tokenData));
						$url = Router::getUrl('validate-registration', array('token' => $token));


						$data = array(
							'themeBaseCss' => ThemeManager::getSelected()->getBaseCssUrl(),
							'themeCustomCss' => ThemeManager::getSelected()->getCustomCssUrl(),
							'mainCssUrl' => Plugin::current()->getCssUrl(),
							'logoUrl' =>  Option::get('main.logo') ? USERFILES_PLUGINS_URL . 'main/' . Option::get('main.logo') : Plugin::current()->getStaticUrl() . 'img/hawk-logo.png',
							'sitename' => Option::get('main.title'),
							'url' => $url
						);
						if(Option::get('main.confirm-email-content')){
							$mailContent = View::makeFromString(Option::get('main.confirm-email-content'), $data);
						}
						else{
							$mailContent = View::make(Plugin::current()->getView('registration-validation-email.tpl'), $data);
						}

						$mail = new Mail();
						$mail->from(Option::get('main.mailer-from'))
							 ->fromName(Option::get('main.mailer-from-name'))
							 ->to($user->email)
							 ->html($mailContent)
							 ->subject(Lang::get('main.register-email-title', array('sitename' => Option::get('main.title'))))
							 ->send();
						
						$form->response(Form::STATUS_SUCCESS, Lang::get('main.register-send-email-success'));
					}
					else{
						// validate the registration
						$form->response(Form::STATUS_SUCCESS, Lang::get('main.register-success'));
					}
				}
				catch(Exception $e){
					$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('main.register-error') );
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

		if(!$user){
			$status = 'danger';
			$messageKey = "main.validate-registration-unknown-error";
		}
		else{
			try{
				$user->set('active', 1);
				$user->save();

				$status = 'success';
				$messageKey = 'main.register-success';
			}
			catch(Exception $e){
				$status = 'danger';
				$messageKey = 'main.validate-registration-error';
			}
		}

		$this->addJavaScriptInline("app.ready(function(){app.notify('$status', '". addcslashes(Lang::get($messageKey), "'") . "');})");

		return MainController::getInstance()->compute('main');

	}
	
	/**
	 * Sign-out
	 */
	public function logout(){
		session_destroy();
		setcookie("PHPSESSID", "", time() - 1, '/');
		header("Location: ./");
		exit;
	}

}