<?php
/**********************************************************************
 *    						Login.ctrl.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
	
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
					"nofieldset" => true,			
					
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
			'onsuccess' => 'location.reload()',
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
			
		);
		
		$form = new Form($param);
		if(!$form->submitted()){
			return View::make($this->theme->getView('dialogbox.tpl'), array(
				'page' => $form->__toString(),
				'icon' => 'sign-in',
				'title' => Lang::get('main.login-form-title'),
				'width' => '400px',
			));
		}
		else{			
		}
	}
	
	/*_______________________________________________________
	
						Logout
	_______________________________________________________*/
	public function logout(){
		session_destroy();
		setcookie("PHPSESSID", "", time() - 1, '/');
		header("Location: ./");
		exit;
	}

}