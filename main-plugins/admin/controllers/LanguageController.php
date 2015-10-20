<?php
/**
 * LanguageController.php
 */

namespace Hawk\Plugins\Admin;

/**
 * This class is the controller for language key actions
 */
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

		if(Request::getParams('filters')) {
			setcookie('languages-filters', Request::getParams('filters'), 0, '/');
			$filters = json_decode(Request::getParams('filters'), true);
		}
		elseif(Request::getCookies('languages-filters')){
			$filters = json_decode(Request::getCookies('languages-filters'), true);
		}
		
		return $filters;	 	
	}
	
	
	/**
	 * Display the main page
	 */
	public function index(){		
		$filters = $this->getFilters();
		
		Lang::addKeysToJavaScript("language.confirm-delete-lang", "language.confirm-delete-key");
		
		$this->addJavaScript( Plugin::current()->getJsUrl('languages.js'));

		return LeftSidebarTab::make(array(
			'icon' => 'flag',
			'title' => Lang::get('language.lang-page-name'),			
			'page' => array(
				'content' => $this->compute('editKeys')
			),
			'sidebar' => array(
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
				$translations = Request::getBody('translation');
				if(!empty($translations[$filters['tag']])){
					foreach($translations[$filters['tag']] as $langKey => $translation){
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
				}
				
				Log::info('The translations has been updated');
				return $form->response(Form::STATUS_SUCCESS, Lang::get('language.update-keys-success'));
			}
			catch(DBException $e){
				Log::error('An error occured while updating translations : ' . $e->getMessage());
				return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('language.update-keys-error'));
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
                        'default' => self::DEFAULT_KEY_PLUGIN,                        
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
                'placeholder' => Lang::get('language.key-form-translation-placeholder', array('tag' => $language->tag)),
                'rows' => 3
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
			try{
				$key = self::DEFAULT_KEY_PLUGIN . '.' . $form->getData('key');
				if(Lang::exists($key)){
					$form->error('key', Lang::get('language.key-already-exists'));
					return $form->response(Form::STATUS_CHECK_ERROR);
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

				Log::info('A new language key has been added');
				return $form->response(Form::STATUS_SUCCESS);
			}
			catch(Exception $e){
				Log::error('An error occured while adding a language key : ' . $e->getMessage());
				return $form->response(Form::STATUS_ERROR);
			}
		}
	}


	/**
	 * Delete a translation key
	 * */
	public function deleteTranslation(){
		try{
			Language::getByTag($this->tag)->removeTranslations(array(
				$this->plugin => array($this->key)
			));
			Log::info('A translation has been reset : ' . $this->plugin . '.' . $this->key);
		}
		catch(Exception $e){
			Log::error('An error occured while reseting the language key ' . $this->plugin . '.' . $this->key);
		}	
	}
	
	/**
	 * Display the list of the translation keys
	 * @param array $filters The filters to display the list
	 */
	public function listKeys($filters = array()){
		if(empty($filters)){
			$filters = $this->getFilters();
		}
			
		
		// Find all files in main-plugin, plugins dans userfiles
		$files = array();
		$dirs = array(MAIN_PLUGINS_DIR, PLUGINS_DIR, USERFILES_PLUGINS_DIR . Lang::TRANSLATIONS_DIR);
		foreach($dirs as $dir){
			if(is_dir($dir)){
				$result = FileSystem::find($dir, '*.*.lang', FileSystem::FIND_FILE_ONLY);
			}
			
			foreach($result as $file){
				list($plugin, $language, $ext) = explode('.', basename($file));
				
				if(empty($files[$plugin])){
					$files[$plugin] = array();
				}

				if(empty($files[$plugin][$language])){
					$files[$plugin][$language] = array();
				}
				$files[$plugin][$language][$dir == USERFILES_PLUGINS_DIR . Lang::TRANSLATIONS_DIR ? 'translation' : 'origin'] = $file;
			}
		}

		$keys = array();
		foreach($files as $plugin => $languages){					
			foreach($languages as $tag => $paths){
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
			'action' => Router::getUri('language-keys-list'),
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
					'href' => Router::getUri('edit-language', array('tag' => 'new')),
					'target' => 'dialog',
					'class' => 'btn-success'
				),
				
				array(
					'href' => Router::getUri('import-language-keys'),
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
						return "<span class='icon icon-undo text-danger delete-translation' title='" . Lang::get('language.delete-translation-btn') . "' data-key='$line->langKey'></span>";						
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
		$activeLanguages = Language::getAllActive();

		$language = Language::getByTag($this->tag);

		$param = array(
			'id' => 'language-form',
			'object' => $language,
			'fieldsets' => array(
				'form' => array(
					'nofieldset' => true,
					
					new TextInput(array(
						'name' => 'tag',
						'label' => Lang::get('language.lang-form-tag-label'),
						'maxlength' => 2,
						'required' => true,
						'unique' => true,
					)),
					
					new TextInput(array(
						'name' => 'label',
						'label' => Lang::get('language.lang-form-label-label'),
						'required' => true,
					)),		

					new CheckboxInput(array(
						'name' => 'active',
						'label' => Lang::get('language.lang-form-active-label'),
						'noDisplayed' => (count($activeLanguages) <= 1 && $language->active) || $language->isDefault
					))
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),
					)),
					
					 
					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'onclick' => 'app.dialog("close")'
					)),
				)
			),
			'onsuccess' => 'app.dialog("close"); app.load(app.getUri("manage-languages"));'
		);
		
		$form = new Form($param);
		
		if(!$form->submitted()){
			return View::make(Theme::getSelected()->getView('dialogbox.tpl'), array(
				'icon' => 'flag',
				'title' => $form->new ? Lang::get('language.add-lang-form-title') : Lang::get('language.edit-lang-form-title'),
				'page' => $form
			));
		}
		else{
			return $form->treat();			
		}
	}
	
	/**
	 * Delete a language
	 */
	public function deleteLanguage(){
		try{
			$language = Language::getByTag($this->tag);
			if(Option::get('main.language') == $this->tag){
				// Set a new default language
				$newDefault = Language::getAllActive()[0];
				Option::set('main.language', $newDefault->tag);
			}
			$language->delete();		

			Log::info('The language ' . $this->tag . ' has been removed');
		}
		catch(Exception $e){
			Log::error('An error occured while removing the language ' . $this->tag . ' : ' . $e->getMessage());
		}
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
			return View::make(Theme::getSelected()->getView('dialogbox.tpl'), array(
				'icon' => 'flag',
				'title' => Lang::get('language.import-form-title'),
				'page' => $form
			));
		}
		else{
			if($form->check()){
				try{
					$files = Request::getFiles('files');
					foreach($files['name'] as $i => $filename){
						// Check the filename is correct
						if(!preg_match('/^([\w\-]+)\.([a-z]{2})\.lang$/', $filename, $matches)) {
							throw new Exception(Lang::get('language.import-file-name-error'));
						}

						list($m, $plugin, $lang) = $matches;

						// Check the content of the file is valid
						$tmpfile = $files['tmp_name'][$i];
						if(($translations = parse_ini_file($tmpfile)) === false){
							throw new Exception(Lang::get('language.import-file-format-error'));
						}

						Language::getByTag($lang)->saveTranslations(array(
							$plugin => $translations
						));

						unlink($tmpfile);
					}
					
					Log::info('Language files were successfully imported');
					return $form->response(Form::STATUS_SUCCESS);
				}
				catch(Exception $e){
					Log::error('An error occured whiel importing language files : ' . $e->getMessage());
					$form->error('files[]', $e->getMessage());					
					return $form->response(Form::STATUS_CHECK_ERROR);
				}
			}		
		}		
	}
}