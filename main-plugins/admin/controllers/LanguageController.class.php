<?php
class LanguageController extends Controller{
	const DEFAULT_KEY_PLUGIN = 'custom';

	/**
	 * Get the filters of the keys list
	 */
	private function getFilters(){
		$filters = array(
			'tag' => LANGUAGE,
			'keys' => 'all'
		);

		if(isset($_GET['filters'])){
			setcookie('languages-filters', $_GET['filters'], 0, '/');
			$filters = json_decode($_GET['filters'], true);
		}
		elseif(isset($_COOKIE['languages-filters'])){
			$filters = json_decode($_COOKIE['languages-filters'], true);
		}
		
		return $filters;	 	
	}
	
	
	/**
	 * Display the main page
	 */
	public function index(){		
		$filters = $this->getFilters();
		
		Lang::addKeysToJavaScript("language.confirm-delete-lang", "language.confirm-delete-key");
		
		$this->addJavaScript( Plugin::current()->getJsUrl() . 'languages.js');

		return LeftSidebarTab::make(array(
			'icon' => 'flag',
			'title' => Lang::get('language.lang-page-name'),			
			'page' => $this->compute('editKeys'),
			// 'page' => $this->editKeys(),
			'sidebar' => array(
				'size' => 2,
				'widgets' => array(new LanguageFilterWidget($filters), new NewLanguageKeyWidget())
			),
			'tabId' => 'language-manage-page'
		));
	}
							   
	/**
	 * Edit the translations keys
	 */
	public function editKeys($filters = array()){
		if(empty($filters)){
			$filters = $this->getFilters();
		}
		
		$form = new Form(array(
			'id' => 'edit-keys-form',
			'action' => Router::getUri('save-language-keys'),			
			'fieldsets' => array(
				'form' => array(
					'nofieldset' => true,
					
					new HtmlInput(array(
						'name' => 'keyList',
						'value' => $this->compute('listKeys')
					))
				)
			),
			'onsuccess' => 'app.lists["language-key-list"].refresh();'
		));

		if(!$form->submitted()){
			// Display the form
			return $form;
		}
		else{
			// Register the translations
			try{
				$keys = array();
				foreach($_POST['translation'][$filters['tag']] as $langKey => $translation){
					if(!empty($translation)){						
						list($plugin, $key) = explode('.', $langKey);
						$key = str_replace(array('{', '}'), array('[', ']'), $key);
						if(empty($keys[$plugin])){
							$keys[$plugin] = array();
						}
						$keys[$plugin][$key] = $translation;						
					}				
				}

				Language::getByTag($filters['tag'])->saveTranslations($keys);
				
				$form->response(Form::STATUS_SUCCESS, Lang::get('language.update-keys-success'));
			}
			catch(DBException $e){
				$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('language.update-keys-error'));
			}
		}
	}
	


	public function keyForm(){
		$param = array(
            'id' => 'add-lang-key-form',
            'action' => Router::getUri('add-language-key'),
            'fieldsets' => array(
                'form' => array(
                    'nofieldset' => true,
                    
                    new TextInput(array(
                        'name' => 'plugin',
                        'label' => Lang::get('language.key-form-plugin-label'),
                        'readonly' => true,
                        'required' => true,
                        'default' => self::DEFAULT_KEY_PLUGIN
                    )),
                    
                    new TextInput(array(
                        'name' => 'key',
                        'required' => true,
                        'pattern' => '/^[\w\-\_]+$/',
                        'label' => Lang::get('language.key-form-key-label'),
                    )),         
                ),
                
                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),
                ),
            ),
            'onsuccess' => 'app.lists["language-key-list"].refresh();app.forms["add-lang-key-form"].node.get(0).reset();' 
        );

        foreach(Language::getAll() as $language){
            $param['fieldsets']['form'][] = new TextareaInput(array(                
                'name' => "translation[{$language->tag}]",
                'label' => $language->label,
                'placeholder' => Lang::get('language.key-form-translation-placeholder', array('tag' => $language->tag))
            ));
        }

        return new Form($param);
	}

	/**
	 * Add a new language key
	 */
	public function addKey(){
		$form = $this->keyForm();

		if($form->check()){
			$key = self::DEFAULT_KEY_PLUGIN . '.' . $form->getData('key');
			if(Lang::exists($key)){
				$form->error('key', Lang::get('language.key-already-exists'));
				$form->response(Form::STATUS_CHECK_ERROR);
			}

			foreach(Language::getAll() as $language){
				$translation = $form->getData("translation[{$language->tag}]");
				
				if($translation){
					$language->saveTranslations(array(
						self::DEFAULT_KEY_PLUGIN => array(
							$form->getData('key') => $translation
						)
					));					
				}
			}

			$form->response(Form::STATUS_SUCCESS);
		}
	}


	/**
	 * Delete a translation key
	 * */
	public function deleteTranslation(){
		Language::getByTag($this->tag)->removeTranslations(array(
			$this->plugin => array($this->key)
		));
	}
	
	/**
	 * Display the list of the translation keys
	 */
	public function listKeys($filters = array()){
		if(empty($filters)){
			$filters = $this->getFilters();
		}
			
		
		// Find all files in main-plugin, plugins dans userfiles
		$files = array();
		$dirs = array(MAIN_PLUGINS_DIR, PLUGINS_DIR, Lang::TRANSLATIONS_DIR);
		foreach($dirs as $dir){
			$result = array();
			exec('find ' . $dir . ' -name "*.*.lang"', $result);

			foreach($result as $file){
				list($plugin, $language, $ext) = explode('.', basename($file));

				if(empty($files[$plugin])){
					$files[$plugin] = array();
				}
				if(empty($files[$plugin][$language])){
					$files[$plugin][$language] = array();
				}
				$files[$plugin][$language][$dir == Lang::TRANSLATIONS_DIR ? 'translation' : 'origin'] = $file;
			}
		}

		$keys = array();
		foreach($files as $plugin => $languages){					
			foreach($languages as $tag => $paths){
				if($tag == Lang::DEFAULT_LANGUAGE || $tag == $filters['tag']){
					foreach($paths as $name => $file){
						$translations = parse_ini_file($file);
						foreach ($translations as $key => $value) {							
							if(!is_array($value)){
								// This is a single key
								$langKey = "$plugin.$key";
								if(empty($keys[$langKey])){
									$keys[$langKey] = array();
								}
								$keys[$langKey][$tag] = $value;
							}
							else{
								// This is a multiple key
								foreach($value as $multiplier => $val){
									$langKey = $plugin . '.' . $key . '[' . $multiplier . ']';
									if(empty($keys[$langKey])){
										$keys[$langKey] = array();
									}
									$keys[$langKey][$tag] = $val;
								}
							}
						}
					}
				}
			}
		}

		$data = array();
		foreach($keys as $langKey => $values){
			if( $filters['keys'] != 'missing' ||  empty($values[$filters['tag']]) ) {
				$data[] = array(
					'langKey' => $langKey, 
					'origin' => isset($values[Lang::DEFAULT_LANGUAGE]) ? $values[Lang::DEFAULT_LANGUAGE] : '', 
					'translation' => isset($values[$filters['tag']]) ? $values[$filters['tag']] : ''
				);
			}
		}

		$param = array(
			'id' => 'language-key-list',
			'action' => Router::getUri('LanguageController.listKeys'),
			'data' => $data,
			'controls' => array(
				array(
					'type' => 'submit',
					'icon' => 'save',
					'label' => Lang::get('main.valid-button'),
					'class' => 'btn-primary'
				),
				
				array(
					'icon' => 'plus',
					'label' => Lang::get('language.new-lang'),
					'href' => Router::getUri('LanguageController.editLanguage', array('tag' => 'new')),
					'target' => 'dialog',
					'class' => 'btn-success'
				),
				
				array(
					'href' => Router::getUri('LanguageController.import'),
					'target' => 'dialog',
					'icon' => 'download',
					'label' => Lang::get('language.import-btn'),
					'class' => 'btn-info'
				),			
			),
			
			'fields' => array(
				'langKey' => array(
					'label' => Lang::get('language.key-list-key-label'),
				),
				
				'origin' => array(
					'label' => Lang::get('language.key-list-default-translation-label', array('tag' => Lang::DEFAULT_LANGUAGE)),															
				),
				
				'translation' => array(
					'label' => Lang::get('language.key-list-default-translation-label', array('tag' => $filters['tag'])),								
					'display' => function($value, $field, $line) use($filters){
						$key = str_replace(array('[', ']'), array('{', '}'), $line->langKey);

						return "<textarea name='translation[{$filters['tag']}][{$key}]' cols='40' rows='5'>$value</textarea>";
					}
				),	

				'clean' => array(
					'search' => false,
					'sort' => false,
					'display' => function($value, $field, $line) {
						return "<span class='fa fa-undo text-danger delete-translation' title='" . Lang::get('language.delete-translation-btn') . "' data-key='$line->langKey'></span>";						
					}
				),			
			)
		);
		
		$list = new ItemList($param);
		
		return $list->__toString();
	}
	
	
	/**
	 * Edit a language
	 */
	public function editLanguage(){
		$param = array(
			'id' => 'language-form',
			'model' => 'Language',
			'reference' => array(Language::getPrimaryColumn() => $this->tag),
			'fieldsets' => array(
				'form' => array(
					'nofieldset' => true,
					
					new TextInput(array(
						'field' => 'tag',
						'label' => Lang::get('language.lang-form-tag-label'),
						'maxlength' => 2,
						'required' => true,
						'unique' => true,
					)),
					
					new TextInput(array(
						'field' => 'label',
						'label' => Lang::get('language.lang-form-label-label'),
						'required' => true,
					)),		
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),
					)),
					
					new DeleteInput(array(
						'name' => 'delete',
						'value' => Lang::get('main.delete-button'),
						'notDisplayed' => $this->tag == 'new'						
					)),
					
					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'onclick' => 'app.dialog("close")'
					)),
				)
			),
			'onsuccess' => 'app.dialog("close"); app.load(app.getUri("LanguageController.index"));'
		);
		
		$form = new Form($param);
		
		if(!$form->submitted()){
			return View::make($this->theme->getView('dialogbox.tpl'), array(
				'icon' => 'flag',
				'title' => $form->new ? Lang::get('language.add-lang-form-title') : Lang::get('language.edit-lang-form-title'),
				'page' => $form
			));
		}
		else{
			$form->treat();			
		}
	}
	
	/**
	 * Delete a language
	 */
	public function deleteLanguage(){
		Language::getByTag($this->tag)->delete();		
	}
	
	/**
	 * Import translation files
	 */
	public function import(){
		$param = array(
			'id' => 'language-import-form',
			'upload' => true,			
			'fieldsets' => array(
				'form' => array(
					'nofieldset' => true,

					new HtmlInput(array(
						'value' => Lang::get('language.import-file-description'),
					)),
					
					new FileInput(array(
						'name' => 'files[]',
						'independant' => true,
						'multiple' => true,
						'required' => true,
						'label' => Lang::get('language.lang-form-import-label'),
					))
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'import',
						'icon' => 'upload',
						'value' => Lang::get('main.import-button'),
					)),				
				)
			),
			'onsuccess' => 'app.dialog("close"); app.lists["language-key-list"].refresh()'
		);
		
		$form = new Form($param);
		
		if(!$form->submitted()){
			return View::make($this->theme->getView('dialogbox.tpl'), array(
				'icon' => 'flag',
				'title' => Lang::get('language.import-form-title'),
				'page' => $form
			));
		}
		else{
			if($form->check()){
				try{
					foreach($_FILES['files']['name'] as $i => $filename){
						// Check the filename is correct
						if(!preg_match('/^([\w\-]+)\.([a-z]{2})\.lang$/', $filename, $matches)) {
							throw new Exception(Lang::get('language.import-file-name-error'));
						}

						list($m, $plugin, $lang) = $matches;

						// Check the content of the file is valid
						$tmpfile = $_FILES['files']['tmp_name'][$i];
						if(($translations = parse_ini_file($filename)) === false){
							throw new Exception(Lang::get('language.import-file-format-error'));
						}

						Language::getByTag($lang)->saveTranslations(array(
							$plugin => $translations
						));

						unlink($tmpfile);						
					}
					
					$form->response(Form::STATUS_SUCCESS);
				}
				catch(Exception $e){
					$form->error('files[]', $e->getMessage());
					$form->response(Form::STATUS_CHECK_ERROR);
				}
			}		
		}		
	}
}