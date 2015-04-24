<?php
class LanguageController extends Controller{
	
	/**
	 * Get the filters of the keys list
	 */
	private function getFilters(){
		$filters = array();
		if(isset($_GET['filters'])){
			setcookie('languages-filters', $_GET['filters'], 0, '/');
			$filters = json_decode($_GET['filters'], true);
		}
		elseif(isset($_COOKIE['languages-filters'])){
			$filters = json_decode($_COOKIE['languages-filters'], true);
		}
		
		if(empty($filters['tag'])){
			$filters['tag'] = Lang::DEFAULT_LANGUAGE;
		}	
		return $filters;	 	
	}
	
	
	/**
	 * Display the main page
	 */
	public function index(){		
		$filters = $this->getFilters();
		
		Lang::addKeysToJavaScript("language.confirm-delete-lang", "language.confirm-delete-key");
		
		return LeftSidebarTab::make(array(
			'icon' => 'flag',
			'title' => Lang::get('language.lang-page-name'),			
			'page' => $this->compute('editKeys'),
			'script' => array(
				'src' => Plugin::current()->getJsUrl() . 'languages.js',
			),
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
						'value' => $this->compute('listKeys')
					))
				)
			),
			'onsuccess' => 'mint.lists["language-key-list"].refresh();'
		));
		if(!$form->submitted()){
			return $form;
		}
		else{
			try{
				foreach($_POST['translation'][$filters['tag']] as $keyId => $translation){
					if(!empty($translation)){
						$model = new LanguageTranslation();
						$model->set('keyId', $keyId);
						$model->set('languageTag', $filters['tag']);
						$model->set('translation', $translation);
						$model->save();
					}				
				}
				
				Language::getByTag($filters['tag'])->generateCacheFiles();
				
				$form->response(Form::STATUS_SUCCESS, Lang::get('language.update-keys-success'));
			}
			catch(DBException $e){
				$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('language.update-keys-error'));
			}
		}
	}
	


	public function keyForm($keyId){
		$param = array(
			'id' => 'edit-lang-key-form-' . $keyId,
			'model' => 'LanguageKey',
			'action' => Router::getUri('LanguageController.editKey', array('keyId' => $keyId)),
			'reference' => array('id' => $keyId),
			'fieldsets' => array(
				'form' => array(
					'nofieldset' => true,
					
					new TextInput(array(
						'name' => 'plugin',
						'label' => Lang::get('language.key-form-plugin-label'),
					)),
					
					new TextInput(array(
						'name' => 'key',
						'label' => Lang::get('language.key-form-key-label'),
					)),			
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button')
					)),
					
					new DeleteInput(array(
						'name' => 'delete',
						'value' => Lang::get('main.delete-button'),
						'notDisplayed' => !$keyId
					)),
					
					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'notDisplayed' => !$keyId,
						'onclick' => 'mint.dialog("close")'
					)),
				),
			),
			'onsuccess' => ($keyId ? 'mint.dialog("close");' : 'mint.forms["edit-lang-key-form-0"].reset();') . 'mint.lists["language-key-list"].refresh();' 
		);

		$translations = LanguageKey::getById($keyId) ? LanguageKey::getById($keyId)->getTranslations() : array();
		foreach(Language::getAll() as $language){
			$param['fieldsets']['form'][] = new TextareaInput(array(				
				'name' => "translation[{$language->tag}]",
				'independant' => true,
				'default' => $translations[$language->tag] ? $translations[$language->tag]->translation : '',
				'label' => $language->label,
			));
		}
		
		$form = new Form($param);

		return $form;

	}

	/**
	 * Edit one translation key
	 */
	public function editKey(){	
		$form = $this->keyForm($this->keyId);
		if(!$form->submitted()){
			return View::make($this->theme->getView('dialogbox.tpl'), array(
				'page' => $form,
				'title' => Lang::get('language.' . ($form->new ? 'key-form-add-title' : 'key-form-edit-title')),
			));
		}
		else{
			if($form->submitted() == "delete"){
				$form->delete();
			}
			else{
				if($form->check()){
					try{
						$keyId = $form->register(Form::NO_EXIT);
						foreach($_POST['translation'] as $tag => $translation){
							if(!empty($translation)){
								$tr = new LanguageTranslation();
								$tr->set('keyId', $keyId);
								$tr->set('languageTag', $tag);
								$tr->set('translation', $translation);
								$tr->save();
								
								Language::getByTag($tag)->generateCacheFiles();
							}
						}						
						
						$form->response(Form::STATUS_SUCCESS);
					}
					catch(Exception $e){
						$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('language.key-already-exists'));
					}
				}
			}
		}
	}
	
	/**
	 * Delete a translation key
	 * */
	public function deleteKey(){
		LanguageKey::getById($this->keyId)->delete();	
	}
	
	/**
	 * Display the list of the translation keys
	 */
	public function listKeys($filters = array()){
		if(empty($filters)){
			$filters = $this->getFilters();
		}
			
		
		$filter = null;
		if($filters['keys'] == 'missing'){
			$filter = new DBExample(array(
				'$or' => array(
					array('T.translation' => ''),
					array('T.translation' => '$null')
				)
			));
		}		
				
		$param = array(
			'id' => 'language-key-list',
			'action' => Router::getUri('LanguageController.listKeys'),
			'table' => LanguageKey::getTable() . ' K 
						LEFT JOIN ' . LanguageTranslation::getTable() . ' DT ON DT.keyId = K.id AND DT.languageTag = "' . Lang::DEFAULT_LANGUAGE. '"
						LEFT JOIN ' . LanguageTranslation::getTable() . ' T ON T.keyId = K.id AND T.languageTag = ' . DB::escape($filters['tag']),
			'reference' => array('K.id' => 'id'),
			'filter' => $filter,
			'sorts' => array('langKey' => DB::SORT_ASC),
			'group' => array('langKey'),
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
				
				array(
					'href' => Router::getUri('LanguageController.export'),
					'target' => 'dialog',
					'icon' => 'upload',
					'label' => Lang::get('language.export-btn'),
					'class' => 'btn-info'
				),
			),
			
			'fields' => array(
				'actions' => array(
					'independant' => true,
					'search' => false,
					'sort' => false,
					'display' => function($value, $field, $line){
						return "<span class='fa fa-pencil text-primary edit-key' title='" . Lang::get('language.key-list-edit-btn') . "' href='" . Router::getUri('edit-language-key', array('keyId' => $line->id)) . "' target='dialog' ></span>
								<span class='fa fa-close text-danger delete-key' title='" . Lang::get('language.key-list-delete-btn') . "' data-key='$line->id'></span>";						
					}
				),
				
				'langKey' => array(
					'field' => 'CONCAT(K.plugin, ".", K.key)',
					'label' => Lang::get('language.key-list-key-label'),
				),
				
				'defaultTranslation' => array(
					'field' => 'DT.translation',
					'label' => Lang::get('language.key-list-default-translation-label', array('tag' => Lang::DEFAULT_LANGUAGE)),															
				),
				
				'translation' => array(
					'field' => 'T.translation',
					'label' => Lang::get('language.key-list-default-translation-label', array('tag' => $filters['tag'])),								
					'display' => function($value, $field, $line) use($filters){
						return "<textarea name='translation[{$filters['tag']}][{$line->id}]' cols='40' rows='5'>$value</textarea>";
					}
				),				
			)
		);
		
		$list = new ItemList($param);
		
		return $list;
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
						'onclick' => 'mint.dialog("close")'
					)),
				)
			),
			'onsuccess' => 'mint.dialog("close"); mint.load(mint.getUri("LanguageController.index"));'
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
			'onsuccess' => 'mint.dialog("close"); mint.lists["language-key-list"].refresh()'
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
					foreach($_FILES['files']['tmp_name'] as $tmpname){
						try{
							Language::importFile($tmpname);
							unlink($tmpname);
						}
						catch(Exception $e){
							$form->error('files[]', Lang::get('language.import-file-format-error'));
							throw $e;
						}
					}
					
					foreach(Language::getAll() as $language){
						$language->generateCacheFiles();
					}
					
					$form->response(Form::STATUS_SUCCESS);
				}
				catch(Exception $e){
					$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : '');
				}
			}		
		}		
	}
	
	
	/**
	 * Export translations from the database to files
	 */
	public function export(){
		
	}
}

Lang::load('language');
