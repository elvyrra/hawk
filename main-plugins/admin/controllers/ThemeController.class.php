<?php


class ThemeController extends Controller{

	/**
	 * Display the main page of themes
	 */
	public function index(){
		
		$tabs = array(
			'select' => array(
				'title' => Lang::get('admin.theme-tab-select-title'),
				'content' => $this->compute('listThemes'),
			),
			'customize' => array(
				'title' => Lang::get('admin.theme-tab-basic-custom-title'),
				'content' => $this->compute('customize'),
			),			
			'css' => array(
				'title' => Lang::get('admin.theme-tab-advanced-custom-title'),
				'content' => $this->compute('css'),
			),
			'medias' => array(
				'title' => Lang::get('admin.theme-tab-medias-title'),
				'content' => $this->compute('medias'),
			),
			'menu' => array(
				'title' => Lang::get('admin.theme-tab-menu-title'),
				'content' => $this->compute('menu')
			)
		);

		$this->addJavaScript(Plugin::current()->getJsUrl() . "themes.js");
		$this->addCss(Plugin::current()->getCssUrl() . "themes.css");

		Lang::addKeysToJavaScript("admin.theme-delete-confirm");
		return View::make(Plugin::current()->getView("themes.tpl"), array(
			'tabs' => $tabs
		));
	}


	

	/**
	 * Display the list of available themes to choose one
	 */
	public function listThemes(){
		$themes = ThemeManager::getAll();
		$selectedTheme = ThemeManager::getSelected();	

		Lang::addKeysToJavaScript("admin.theme-update-reload-page-confirm");

		return View::make(Plugin::current()->getView("themes-list.tpl"), array(
			'themes' => ThemeManager::getAll(),
			'selectedTheme' => ThemeManager::getSelected(),
		));
	}

	

	/**
	 * Select a theme to be active
	 */
	public function select(){
		ThemeManager::setSelected($this->name);
	}


	

	/**
	 * Customize the current selected theme
	 */
	public function customize(){
		$theme = ThemeManager::getSelected();
		$variables = $theme->getCssVariables();
		
		if(!empty($_GET['reset'])){
			foreach($variables as $var){
				Option::delete('theme-' . $theme->getName() . '.' . $var['name']);
			}
			$theme->buildCssFile(true);
		}
		
		$options = $options = Option::getPluginOptions('theme-' . $theme->getName());
		$param = array(
			'id' => 'custom-theme-form',
			'upload' => true,
			'action' => Router::getUri('customize-theme'),
			'fieldsets' => array(
				'form' => array(),

				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),
					)),

					new ButtonInput(array(
						'name' => 'reset',
						'value' => Lang::get('admin.theme-custom-reset'),
						'class' => 'btn-default',
						'onclick' => 'app.load(app.getUri("customize-theme") + "?reset=1", {
										selector : "#admin-themes-customize-tab",
										onload : function(){
											app.forms["custom-theme-form"].node.trigger("success" , {href : "' . $theme->getBaseCssUrl() . '?' . time() .'"})
										}
									})'
					))
				)
			),

			'onsuccess' => '$("#theme-base-stylesheet").attr("href", data.href)',
		);

		
		foreach($variables as $var){
			switch($var['type']){
				case 'color' :
					$input = new ColorInput(array(
						'name' => $var['name'],
						'label' => $var['description'],
						'value' => !empty($options[$var['name']]) ? $options[$var['name']] : $var['default']
					));
				break;

				case 'file' :
					$input = new FileInput(array(
						'name' => $var['name'],
						'label' => $var['description'],						
					));
				break;

				default :
					$input = new TextInput(array(
						'name' => $var['name'],
						'label' => $var['description'],
						'value' => !empty($options[$var['name']]) ? $options[$var['name']] : $var['default']
					));
				break;
			}

			$param['fieldsets']['form'][] = $input;
		}

		$form = new Form($param);
		$submitted = $form->submitted();
		if(!$submitted){
			return $form;
		}	
		else{	
			try{
				foreach($variables as $var){										
					if($var['type'] == 'file'){						
						$upload = Upload::getInstance($var['name']);
						if($upload){
							$dir = $theme->getBuildDirname() . 'medias/';
							if(!is_dir( $dir)){
								mkdir( $dir, 0755);
							}

							$file = $upload->getFile();
							$upload->move($file, $dir);
						
							Option::set('theme-' . $theme->getName() . '.' . $var['name'], $theme->getMediasUrl() . $filename);
						}
					}
					else{
						Option::set('theme-' . $theme->getName() . '.' . $var['name'], $form->getData($var['name']));					
					}
				}

				$theme->buildCssFile(true);
				$form->addReturn('href', $theme->getBaseCssUrl() . '?' . time());
				return $form->response(Form::STATUS_SUCCESS);
			}
			catch(Exception $e){
				return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : '');
			}
		}
	}


	/**
	 * Customize the css of the current selected theme
	 */
	public function css(){
		$file = ThemeManager::getSelected()->getCustomCssFile();
		$css = is_file($file) ? file_get_contents($file) : '';
		$param = array(
			'id' => 'theme-css-form',
			'action' => Router::getUri('theme-css'),
			'fieldsets' => array(
				'_submits' => array(			
					new HtmlInput(array(
						'name' => 'desctiption',
						'value' => Lang::get('admin.theme-css-description'),
					)),

					new SubmitInput(array(
						'class' => 'pull-right',
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),					
					))
				),

				'form' => array(
					new TextareaInput(array(
						'name' => 'css',
						'hidden' => true,
						'value' => $css,
						'attributes' => array(
							'data-bind' => 'value : css'
						)
					)),

					new HtmlInput(array(	
						'name' => 'ace',					
						'value' => '<style id="editing-css-computed" data-binding="text: css">' . $css . '</style>
									<div id="theme-css-edit" contenteditable >' . $css . '</div>'
					)),
				)
			)
		);

		$form = new Form($param);

		if(!$form->submitted()){
			return $form;
		}
		else{
			file_put_contents($file, $form->getData('css'));

			$form->addReturn('href', ThemeManager::getSelected()->getCustomCssUrl() . '?' . time());

			return $form->response(Form::STATUS_SUCCESS);
		}
	}


	/**
	 * Media gallery
	 */
	public function medias(){
		$theme = ThemeManager::getSelected();

		$rootDir = $theme->getMediasDir();
		$rootUrl = $theme->getMediasUrl();

		$files = glob($rootDir . '*');		
		$medias = array(
			'image' => array(
				'icon' => 'picture-o',
				'files' => array()
			),
			'audio' => array(
				'icon' => 'music',
				'files' => array(),
			),			
			'other' => array(
				'icon' => 'file',
				'files' => array()
			),
		);
			
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		foreach($files as $file){
			if(is_file($file)){
				$mime = finfo_file($finfo, $file);
				list($category, $precision) = explode('/', $mime);
				if(!in_array($category, array('audio', 'image') )){
					$category = 'other';
				}
				
				$url = $rootUrl . basename($file);
				switch($category){
					case 'image' :
						$medias[$category]['files'][] = array(
							'url' => $url,
							'display' => "<img src='$url' class='media-image-preview' />"
						);
						break;

					default :
						$medias[$category]['files'][] = array(
							'url' => $url,
							'display' => "<i class='fa fa-{$medias[$category]['icon']}'></i>" . basename($file)
						);
						break;
				}				
			}
		}

		Lang::addKeysToJavaScript('admin.theme-delete-media-confirm');
		return View::make(Plugin::current()->getView("theme-medias.tpl"), array(
			'medias' => $medias,				
		));
	}


	public function addMediaForm(){
		$param = array(
			'id' => 'add-theme-media-form',
			'upload' => true,
			'action' => Router::getUri('add-theme-media'),
			'fieldsets' => array(
				'form' => array(
					new FileInput(array(
						'name' => 'medias[]',
						'multiple' => true,
						'required' => true,						
						'nl' => false,
					)),

					new SubmitInput(array(
						'name' => 'valid',
						'icon' => 'upload',
						'value' => Lang::get('admin.theme-add-submit-value'),
					)),
				)
			),
			'onsuccess' => 'app.load(app.getUri("theme-medias"), {selector : "#admin-themes-medias-tab"});'

		);

		return new Form($param);
	}

	/**
	 * Add a new media
	 */
	public function addMedia(){
		$form = $this->addMediaForm();

		if($form->check()){
			$uploader = Upload::getInstance('medias');

			$dir = ThemeManager::getSelected()->getMediasDir();
			if(!is_dir($dir)){
				mkdir($dir, 0755, true);
			}
			foreach($uploader->getFiles() as $file){
				$uploader->move($file, ThemeManager::getSelected()->getMediasDir());
			}

			return $form->response(Form::STATUS_SUCCESS);
		}
	}


	/**
	 * Delete a media of the current theme
	 */
	public function deleteMedia(){
		$filename = urldecode($this->filename);
		FileSystem::remove(ThemeManager::getSelected()->getMediasDir() . $filename);
	}



	public function addThemeForm(){
		$param = array(
			'id' => 'add-theme-form',
			'upload' => true,
			'action' => Router::getUri('add-theme'),
			'fieldsets' => array(
				'form' => array(
					new FileInput(array(
						'name' => 'theme',
						'required' => true,
						'extensions' => array('zip'),
						'nl' => false,
					)),

					new SubmitInput(array(
						'name' => 'valid',
						'icon' => 'upload',
						'value' => Lang::get('admin.theme-add-submit-value'),						
					)),
				)
			),
			'onsuccess' => 'app.load(app.getUri("available-themes"), { selector : $("#admin-themes-select-tab")} );'

		);

		return new Form($param);
	}


	/**
	 * Add a new theme
	 */
	public function add(){
		$form = $this->addThemeForm();
		if($form->check()){
			$uploader = Upload::getInstance('theme');

			if($uploader){
				$zip = new ZipArchive;
				$file = $uploader->getFile();
				$zip->open($file->tmpFile);
				$zip->extractTo(THEMES_DIR);
				$zip->close();
			}

			return $form->response(Form::STATUS_SUCCESS);
		}
	}


	/**
	 * Delete a theme 
	 */
	public function delete(){
		$theme = ThemeManager::get($this->name);
		if($theme->isRemovable()){
			$dir = $theme->getRootDirname();
			FileSystem::remove($dir);
		}
	}


	/**
	 * Customize the menu
	 */
	public function menu(){
		$items = MenuItem::getAll();

		$form = new Form(array(
			'id' => 'set-menus-form',
			'action' => Router::getUri('set-menu'),
			'fields' => array(
				new HiddenInput(array(
					'name' => 'data',
					'default' => json_encode($items, JSON_NUMERIC_CHECK),
					'attributes' => array('data-bind' => 'value: ko.toJSON(items)'),
				)),				

				new SubmitInput(array(
					'name' => 'valid',
					'value' => Lang::get('main.valid-button'),
				)),
			),

			'onsuccess' => 'app.refreshMenu()'
		));

		if(!$form->submitted()){
			Lang::addKeysToJavaScript('admin.plugins-advert-menu-changed');
			return View::make(Plugin::current()->getView('sort-main-menu.tpl'), array(
				'form' => $form,				
			));
		}
		else{
			try {
				$items = MenuItem::getAll('id');

				$data = json_decode($form->getData('data'), true);

				foreach($data as $line){
					$item = $items[$line['id']];
					$item->set(array(
						'active' => $line['active'],
						'parentId' => $line['parentId'],
						'order' => $line['order']
					));
					$item->save();
				}

				return $form->response(Form::STATUS_SUCCESS, Lang::get('admin.sort-menu-success'));
			} 
			catch (Exception $e) {
				return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.sort-menu-error'));
			}
			
		}
	}
}