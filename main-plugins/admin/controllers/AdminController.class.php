<?php

class AdminController extends Controller{
	const MAX_LOGO_SIZE = 200000; // 200 Ko
	const MAX_FAVICON_SIZE = 20000; // 20 Ko
	
	public function settings(){		
		
		$languages = array_map(function($language){ return $language->label; }, Language::getAll('tag'));

		$roleObjects = Role::getListByExample(new DBExample(array(
			'id' => array('$ne' => 0)
		)), 'id');
		$roles = array();
		foreach($roleObjects as $role){
			$roles[$role->id] = Lang::get("roles.role-$role->id-label");
		}

		$menus = Menu::getAvailableMenus();

		$menuItems = array();
		foreach($menus as $menu){
			foreach($menu->visibleItems as $item){
				if(!$item->target || $item->target == 'newtab'){
					$menuItems[$item->action] = $menu->label . " &gt; " . $item->label;
				}
			}
		}

		$param = array(
			'id' => 'settings-form',
			'upload' => true,
			'labelWidth' => '250px',
			'fieldsets' => array(
				'main' => array(
					'nofieldset' => true,
					
					new TextInput(array(
						'name' => 'main.title',
						'required' => true,
						'default' => Option::get('main.title'),
						'label' => Lang::get('admin.settings-title-label')
					)),
					
					new SelectInput(array(
						'name' => 'main.language',
						'required' => true,
						'options' => $languages,
						'default' => Option::get('main.language'),						
						'label' => Lang::get('admin.settings-language-label'),
					)),
					
					new SelectInput(array(
						'name' => 'main.timezone',
						'required' => true,
						'options' => array_combine(DateTimeZone::listIdentifiers(), DateTimeZone::listIdentifiers()),
						'default' => Option::get('main.timezone'),
						'label' => Lang::get('admin.settings-timezone-label')
					)),
					
					new SelectInput(array(
						'name' => 'main.currency',
						'required' => true,
						'options' => array(
							'EUR' => 'Euro (€)',
							'USD' => 'US Dollar ($)'
						),
						'default' => Option::get('main.currency'),
						'label' => Lang::get('admin.settings-currency-label')
					)),	

					new IntegerInput(array(
						'name' => 'main.tabsNumber',
						'default' => Option::get('main.tabsNumber') ? Option::get('main.tabsNumber') : 10,
						'minimum' => 1,
						'maximum' => 20,
						'label' => Lang::get('admin.settings-tabs-number-label'),
					)),

					new FileInput(array(
						'name' => 'logo',
						'label' => Lang::get('admin.settings-logo-label'),
						'after' => Option::get('main.logo') ? '<img src="/userfiles/plugins/main/'.Option::get('main.logo').'" class="settings-logo-preview" />' : '',
						'maxSize' => 200000,
						'extensions' => array('gif', 'png', 'jpg', 'jpeg')
					)),
					
					new FileInput(array(
						'name' => 'favicon',
						'label' => Lang::get('admin.settings-favicon-label'),
						'after' => Option::get('main.favicon') ? '<img src="/userfiles/plugins/main/'.Option::get('main.favicon').'" class="settings-favicon-preview" />' : '',
						'maxSize' => 20000,
						'extensions' => array('gif', 'png', 'jpg', 'jpeg', 'ico')
					))
				),
				
				'home' => array(
					'nofieldset' => true,
					
					new RadioInput(array(
						'name' => 'main.home-page-type',
						'options' => array(
							// 'default' => Lang::get('admin.settings-home-page-type-default'),
							'custom' => Lang::get('admin.settings-home-page-type-custom'),
							'page' => Lang::get('admin.settings-home-page-type-page'),
						),
						'default' => Option::get('main.home-page-type') ? Option::get('main.home-page-type') : 'default',
						'label' => Lang::get('admin.settings-home-page-type-label'),
						'layout' => 'vertical',
						'attributes' => array(
							'data-bind' => 'checked : homePage.type'
						)
					)),
					
					new WysiwygInput(array(
						'name' => 'main.home-page-html',
						'id' => 'home-page-html',
						'label' => Lang::get('admin.settings-home-page-html-label'),
						'default' => Option::get('main.home-page-html'),
					)),

					new SelectInput(array(
						'name' => 'main.home-page-item',
						'id' => 'home-page-item',
						'label' => Lang::get('admin.settings-home-page-item-label'),
						'options' => $menuItems
					)),
					
					new CheckboxInput(array(
						'name' => 'main.open-last-tabs',
						'label' => Lang::get('admin.settings-open-last-tabs'),
						'default' => Option::get('main.open-last-tabs'),
						'dataType' => 'int'
					)),					
				),
				
				'users' => array(
					'nofieldset' => true,
					
					new RadioInput(array(
						'name' => 'main.allow-guest',
						'options' => array(
							0 => Lang::get('main.no-txt'),
							1 => Lang::get('main.yes-txt'),
						),
						'default' => Option::get('main.allow-guest') ? Option::get('main.allow-guest') : 0,
						'label' => Lang::get('admin.settings-allow-guest-label')
					)),
					
					new RadioInput(array(
						'name' => 'main.open-register',
						'options' => array(
							0 => Lang::get('admin.settings-open-register-off'),
							1 => Lang::get('admin.settings-open-register-on'),
						),
						'layout' => 'vertical',
						'label' => Lang::get('admin.settings-open-registers-label'),
						'default' => Option::get('main.open-register') ?  Option::get('main.open-register') : 0,
						'attributes' => array(
							'data-bind' => 'checked: register.open'
						)
					)),
					
					new CheckboxInput(array(
						'name' => 'main.confirm-register-email',
						'label' => Lang::get('admin.settings-confirm-email-label'),
						'default' => Option::get('main.confirm-register-email'),
						'dataType' => 'int',
						'attributes' => array(
							'data-bind' => 'checked: register.checkEmail'
						)
					)),

					new WysiwygInput(array(
						'name' => 'main.confirm-email-content',
						'default' => Option::get('main.confirm-email-content'),
						'label' => Lang::get('admin.settings-confirm-email-content-label'),
						'labelWidth' => 'auto',
					)),
					
					new CheckboxInput(array(
						'name' => 'main.confirm-register-terms',
						'label' => Lang::get('admin.settings-confirm-terms-label'),
						'default' => Option::get('main.confirm-register-terms'),
						'dataType' => 'int',
						'labelWidth' => 'auto',
						'attributes' => array(
							'data-bind' => 'checked: register.checkTerms'
						)
					)),

					new WysiwygInput(array(
						'name' => 'main.terms',
						'label' => Lang::get('admin.settings-terms-label'),
						'labelWidth' => 'auto',
						'default' => Option::get('main.terms'),
					)),

					new SelectInput(array(
						'name' => 'roles.default-role',
						'label' => Lang::get('admin.settings-default-role-label'),
						'options' => $roles,
						'default' => Option::get('roles.default-role')
					)),

					// new IntegerInput(array(
					// 	'name' => 'main.session-lifetime',
					// 	'label' => Lang::get('admin.settings-session-lifetime-label'),
					// 	'default' => Option::get('main.session-lifetime') ? Option::get('main.session-lifetime') : 0,
					// 	'minimum' => 0,
					// 	'after' => Lang::get('admin.settings-session-lifetime-description')
					// ))


				),
				
				'email' => array(
					'nofieldset' => true,

					new EmailInput(array(
						'name' => 'main.mailer-from',
						'default' => Option::get('main.mailer-from') ? Option::get('main.mailer-from') : Session::getUser()->email,
						'label' => Lang::get('admin.settings-mailer-from-label')
					)),					

					new TextInput(array(
						'name' => 'main.mailer-from-name',
						'default' => Option::get('main.mailer-from-name') ? Option::get('main.mailer-from-name') : Session::getUser()->getDisplayName(),
						'label' => Lang::get('admin.settings-mailer-from-name-label')
					)),

					new SelectInput(array(
						'name' => 'main.mailer-type',
						'default' => Option::get('main.mailer-type'),
						'options' => array(
							'mail' => Lang::get('admin.settings-mailer-type-mail-value'),
							'smtp' => Lang::get('admin.settings-mailer-type-smtp-value'),
							'pop3' => Lang::get('admin.settings-mailer-type-pop3-value')
						),
						'label' => Lang::get('admin.settings-mailer-type-label'),
						'attributes' => array(
							'data-bind' => 'value: mail.type'
						)
					)),
					
					new TextInput(array(
						'name' => 'main.mailer-host',
						'default' => Option::get('main.mailer-host'),
						'label' => Lang::get('admin.settings-mailer-host-label'),						
					)),
					
					new IntegerInput(array(
						'name' => 'main.mailer-port',
						'default' => Option::get('main.mailer-port'),
						'label' => ':',
						'labelWidth' => 'auto',
						'size' => 4,
						'nl' => false
					)),
					
					new TextInput(array(
						'name' => 'main.mailer-username',
						'default' => Option::get('main.mailer-username'),
						'label' => Lang::get('admin.settings-mailer-username-label'),						
					)),
					
					new PasswordInput(array(
						'name' => 'main.mailer-password',
						'encrypt' => 'Crypto::aes256_encode',
						'decrypt' => 'Crypto::aes256_decode',
						'default' => Option::get('main.mailer-password'),
						'label' => Lang::get('admin.settings-mailer-password-label'),						
					)),
					
					new SelectInput(array(
						'name' => 'main.smtp-secured',
						'options' => array(
							'' => Lang::get('main.no-txt'),
							'ssl' => 'SSL',
							'tsl' => 'TSL'
						),
						'label' => Lang::get('admin.settings-smtp-secured-label')
					))
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'save',
						'value' => Lang::get('main.valid-button'),
						'class' => 'pull-right'
					)),				
				),
			),
		);
		
		$form = new Form($param);
		if(!$form->submitted()){
			$this->addCss(Plugin::current()->getCssUrl() . 'settings.css');

			$page = $form->wrap(View::make(Plugin::current()->getViewsDir() . 'settings.tpl', array(
				'form' => $form,	
			)));
			
			$this->addJavaScript(Plugin::current()->getJsUrl() . 'settings.js');
			return NoSidebarTab::make(array(
				'icon' => 'cogs',
				'title' => Lang::get('admin.settings-page-name'),
				'description' => Lang::get('admin.settings-page-description'),
				'page' => $page				
			));
		}
		else{			
			try{				
				if($form->check()){					
					// register scalar values
					foreach($form->fields as $name => $field){
						if(!$field instanceof FileInput && !$field instanceof ButtonInput){
							$value = $field->dbvalue();
							if($value === null){
								$value = '0';
							}
							$field->set($value);
							Option::set($name, $value);					
						}
						elseif($field instanceof FileInput){			
							$upload = Upload::getInstance($name);						
							if($upload){							
								try{									
									$file = $upload->getFile();

									
									$dir = Plugin::get('main')->getUserfilesDir();
									
									if(!is_dir($dir)){
										mkdir($dir, 0755);
									}									

									if($name == 'favicon'){
										$basename = uniqid() . '.ico';
										$generator = new PHPICO($file->tmpFile, array(
											array(16, 16),
											array(32, 32),
											array(48, 48),
											array(64, 64),
										));
										$generator->save_ico($dir . $basename);
									}
									else{
										$basename = uniqid() . '.' . $file->extension;
										$upload->move($file, $dir, $basename);	
									}

									// remove the old image
									@unlink($dir . Option::get("main.$name"));
									
									Option::set("main.$name", $basename);
								}
								catch(ImageException $e){
									$form->error($name, Lang::get('form.image-format'));
									throw $e;
								}
							}						
						}
					}					
					
					// Register the favicon
					Log::info('The options of the application has been updated by ' . Session::getUser()->username);
					$form->response(Form::STATUS_SUCCESS, Lang::get('admin.settings-save-success'));
				}
			}
			catch(Exception $e){
				Log::error('An error occured while updating application options');
				$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.settings-save-error'));
			}
		}
	}
	
}