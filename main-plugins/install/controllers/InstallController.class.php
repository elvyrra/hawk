<?php


class InstallController extends Controller{
	
	public function setLanguage(){
		$form = new Form(array(
			'id' => 'install-form',
			'method' => 'get',
			'fieldsets' => array(
				'form' => array(
					new SelectInput(array(
						'name' => 'language',
						'options' => array(
							'en' => 'English',
							'fr' => 'Français'
						),
						'default' => LANGUAGE,
						'label' => Lang::get('install.set-language-label') 
					)),
				),

				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('install.continue-button'),
						'icon' => 'chevron-circle-right',
						'nl' => true
					))
				)
			)
		));

		$body = View::make(Plugin::current()->getView('set-language.tpl'), array(
			'form' => $form
		));

		return MainController::getInstance()->index($body);
	}

	public function settings(){
		$form = new Form(array(
			'id' => 'install-settings-form',
			'labelWidth' => '30em',
			'fieldsets' => array(
				'global' => array(
					'legend' => Lang::get('install.settings-global-legend', null, null, $this->language),
					new TextInput(array(
						'name' => 'title',
						'required' => true,
						'label' => Lang::get('install.settings-title-label', null, null, $this->language),
						'default' => DEFAULT_HTML_TITLE
					)),

					new TextInput(array(
						'name' => 'rooturl',
						'required' => true,
						'label' => Lang::get('install.settings-rooturl-label', null, null, $this->language),
						'placeholder' => 'http://',
						'default' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'],
					)),

					new SelectInput(array(
						'name' => 'timezone',
						'required' => true,
						'options' => array_combine(DateTimeZone::listIdentifiers(), DateTimeZone::listIdentifiers()),
						'default' => DEFAULT_TIMEZONE,
						'label' => Lang::get('install.settings-timezone-label')
					)),

					new SelectInput(array(
						'name' => 'session',
						'required' => true,
						'options' => array(
							'file' => Lang::get('install.settings-session-file-value', null, null, $this->language),
							'database' => Lang::get('install.settings-session-database-value', null, null, $this->language),
							// 'memcache' => Lang::get('install.settings-session-memcache-value', null, null, $this->language)
						),
						'label' => Lang::get('install.settings-session-label', null, null, $this->language),
						'default' => DEFAULT_SESSION_ENGINE
					)),

					new TextInput(array(
						'name' => 'version',
						'required' => true,
						'default' => '0.0.1',
						'label' => Lang::get('install.settings-version-label', null, null, $this->language),
					))

				),

				'database' => array(
					'legend' => Lang::get('install.settings-database-legend', null, null, $this->language),

					new TextInput(array(
						'name' => 'db[host]',
						'required' => true,
						'label' => Lang::get('install.settings-db-host-label', null, null, $this->language),
						'default' => 'localhost',
					)),

					new TextInput(array(
						'name' => 'db[username]',
						'required' => true,
						'label' => Lang::get('install.settings-db-username-label', null, null, $this->language)
					)),

					new PasswordInput(array(
						'name' => 'db[password]',
						'required' => true,
						'label' => Lang::get('install.settings-db-password-label', null, null, $this->language),
					)),

					new TextInput(array(
						'name' => 'db[dbname]',
						'required' => true,
						'pattern' => '/^\w+$/',
						'label' => Lang::get('install.settings-db-dbname-label', null, null, $this->language)
					)),

				),

				'admin' => array(
					'legend' => Lang::get('install.settings-admin-legend', null, null, $this->language),

					new TextInput(array(
						'name' => 'admin[login]',
						'required' => true,
						'pattern' => '/^\w+$/',
						'label' => Lang::get('install.settings-admin-login-label', null, null, $this->language)
					)),

					new EmailInput(array(
						'name' => 'admin[email]',
						'required' => true,
						'label' => Lang::get('install.settings-admin-email-label', null, null, $this->language)
					)),

					new PasswordInput(array(
						'name' => 'admin[password]',
						'required' => true,
						'label' => Lang::get('install.settings-admin-password-label', null, null, $this->language)
					)),

					new PasswordInput(array(
						'name' => 'admin[passagain]',
						'required' => true,
						'compare' => 'admin[password]',
						'label' => Lang::get('install.settings-admin-passagain-label', null, null, $this->language),
					)),
				),

				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('install.install-button', null, null, $this->language),
						'icon' => 'cog fa-spin',
					))
				)
			),
			'onsuccess' => 'location.href = data.rooturl;'
		));

		if(!$form->submitted()){
			// Display the form
			$body =  View::make(Plugin::current()->getView('settings.tpl'), array(
				'form' => $form
			));

			return MainController::getInstance()->index($body);
		}
		else{
			// Make the installation 
			if($form->check()){
				/**
				 * Generate Crypto constants
				 */
				$salt = Crypto::generateKey(24);
				$key = Crypto::generateKey(32);
				$iv = Crypto::generateKey(16);
				$configMode = 'dev';


				/**
				 * Create the database and it tables
				 */
				$tmpfile = tempnam(sys_get_temp_dir(), '');	

				DB::add('tmp', array(
					array(
						'host' => $form->getData('db[host]'),
						'username' => $form->getData('db[username]'),
						'password' => $form->getData('db[password]')
					)
				));

				try{
					DB::get('tmp');
				}
				catch(DBException $e){
					return $form->response(Form::STATUS_ERROR, Lang::get('install.install-connection-error'));
				}

				try{
					$param = array(
						'{{ $dbname }}' => $form->getData('db[dbname]'),
						'{{ $language }}' => $this->language,
						'{{ $timezone }}' => $form->getData('timezone'),
						'{{ $title }}' => Db::get('tmp')->quote($form->getData('title')),
						'{{ $email }}' => Db::get('tmp')->quote($form->getData('admin[email]')),
						'{{ $login }}' => Db::get('tmp')->quote($form->getData('admin[login]')),
						'{{ $password }}' => Db::get('tmp')->quote(Crypto::saltHash($form->getData('admin[password]'), $salt)),
						'{{ $ip }}' => Db::get('tmp')->quote(Request::clientIp())
					);
					$sql = strtr(file_get_contents(Plugin::current()->getRootDir() . 'files/install.sql.tpl'), $param);
					// file_put_contents($tmpfile, $sql);

					Db::get('tmp')->query($sql);

					/**
					 * Create the config file
					 */				
					$param = array(
						'{{ $salt }}' => addcslashes($salt, "'"),
						'{{ $key }}' => addcslashes($key, "'"),
						'{{ $iv }}' => addcslashes($iv, "'"),
						'{{ $configMode }}' => $configMode,
						'{{ $rooturl }}' => $form->getData('rooturl'),
						'{{ $host }}' => $form->getData('db[host]'),
						'{{ $username }}' => $form->getData('db[username]'),
						'{{ $password }}' => $form->getData('db[password]'),
						'{{ $dbname }}' => $form->getData('db[dbname]'),
						'{{ $sessionEngine }}' => $form->getData('session'),
						'{{ $version }}' => $form->getData('version')
					);
					$config = strtr(file_get_contents(Plugin::current()->getRootDir() . 'files/config.php.tpl'), $param);
					file_put_contents(INCLUDES_DIR . 'config.php', $config);


					/**
					 * Create the envrionment config file
					 */
					touch(ROOT_DIR . 'etc/' . $configMode . '.php');

					$form->addReturn('rooturl', $form->getData('rooturl'));
					
					return $form->response(Form::STATUS_SUCCESS, Lang::get('install.install-success'));
				}
				catch(Exception $e){
					return $form->response(Form::STATUS_ERROR, Lang::get('install.install-error'));
				}				
			}
		}
	}
}