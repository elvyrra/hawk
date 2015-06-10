<?php

class AdminController extends Controller{
	const MAX_LOGO_SIZE = 200000; // 200 Ko
	const MAX_FAVICON_SIZE = 20000; // 20 Ko
	
	public function index(){
		
	}
	
	public function settings(){		
		
		
		$languages = array();
		foreach(Language::getAll() as $lang){
			$languages[$lang->tag] = $lang->label;
		}

		$roleObjects = Role::getListByExample(new DBExample(array(
			'id' => array('$ne' => 0)
		)), 'id');
		$roles = array();
		foreach($roleObjects as $role){
			$roles[$role->id] = Lang::get("roles.role-$role->id-label");
		}

		$menus = Menu::getAvailableMenus($user);

		$menuItems = array();
		foreach($menus as $menu){
			foreach($menu->visibleItems as $item){
				$menuItems[$item->action] = $menu->label . " &gt; " . $item->label;
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

					new FileInput(array(
						'name' => 'logo',
						'label' => Lang::get('admin.settings-logo-label'),
						'after' => Option::get('main.logo') ? '<img src="/userfiles/plugins/main/'.Option::get('main.logo').'" height="50" width="50" />' : '',
						'maxSize' => 200000,
						'extensions' => array('gif', 'png', 'jpg', 'jpeg')
					)),
					
					new FileInput(array(
						'name' => 'favicon',
						'label' => Lang::get('admin.settings-favicon-label'),
						'after' => Option::get('main.favicon') ? '<img src="/userfiles/plugins/main/'.Option::get('main.favicon').'" height="50" width="50" />' : '',
						'maxSize' => 20000,
						'extensions' => array('gif', 'png', 'jpg', 'jpeg')
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
					)),
					
					new CheckboxInput(array(
						'name' => 'main.confirm-register-email',
						'label' => Lang::get('admin.settings-confirm-email-label'),
						'default' => Option::get('main.confirm-register-email'),
						'dataType' => 'int'
					)),
					
					new CheckboxInput(array(
						'name' => 'main.confirm-register-terms',
						'label' => Lang::get('admin.settings-confirm-terms-label'),
						'default' => Option::get('main.confirm-register-terms'),
						'dataType' => 'int'
					)),

					new SelectInput(array(
						'name' => 'roles.default-role',
						'label' => Lang::get('main.settings-default-role-label'),
						'options' => $roles,
						'default' => Option::get('roles.default-role')
					))
				),
				
				'email' => array(
					'nofieldset' => true,
					
					new TextInput(array(
						'name' => 'main.stmp-host',
						'default' => Option::get('main.smtp-host'),
						'label' => Lang::get('admin.settings-smtp-host-label'),						
					)),
					
					new IntegerInput(array(
						'name' => 'main.smtp-port',
						'default' => Option::get('main.smpt-port'),
						'label' => ':',
						'labelWidth' => 'auto',
						'size' => 4,
						'nl' => false
					)),
					
					new TextInput(array(
						'name' => 'main.stmp-username',
						'default' => Option::get('main.smtp-username'),
						'label' => Lang::get('admin.settings-smtp-username-label'),						
					)),
					
					new PasswordInput(array(
						'name' => 'main.stmp-password',
						'encrypt' => 'Crypto::aes256_encode',
						'decrypt' => 'Crypto::aes256_decode',
						'default' => Option::get('main.smtp-password'),
						'label' => Lang::get('admin.settings-smtp-password-label'),						
					)),
					
					new SelectInput(array(
						'name' => 'main.smtp-secured',
						'options' => array(
							'none' => Lang::get('main.no-txt'),
							'SSL' => 'SSL',
							'TSL' => 'TSL'
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
			$page = $form->wrap(View::make(Plugin::current()->getViewsDir() . 'settings.tpl', array(
				'form' => $form,	
			)));
			
			$this->addJavaScript(Plugin::current()->getJsUrl() . 'settings.js');
			return RightSidebarTab::make(array(
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
							$value = $form->getData($name);
							if($value === null){
								$value = '0';
							}
							Option::set($name, $field->dbvalue());					
						}
						else{				

							$upload = Upload::getInstance($name);						
							if($upload){							
								try{									
									$file = $upload->getFile();

									$image = Image::getInstance($file->tmpFile);										
									while($image->getFileSize() > $field->maxSize){										
										// Compress the image
										$file->tmpFile = dirname($file->tmpFile) . '/' . uniqid();
										$image = $image->compress(50, $file->tmpFile);
									}

									$basename = uniqid() . '.' . $file->extension;
									$dir = Plugin::get('main')->getUserfilesDir();
									if(!is_dir($dir)){
										mkdir($dir, 0755);
									}
									$upload->move($file, $dir, $basename);	

									// remove the old image
									@unlink($dir . Option::get("main.$name"));
									
									Option::set("main.$name", $basename);
								}
								catch(ImageException $e){
									$form->error($prop, Lang::get('form.image-format'));
									throw $e;
								}
							}						
						}
					}					
					
					// Register the favicon
					$form->response(Form::STATUS_SUCCESS, Lang::get('admin.settings-save-success'));
				}
			}
			catch(Exception $e){
				$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.settings-save-error'));
			}
		}
	}
	
}